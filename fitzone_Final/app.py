from flask import Flask, render_template, request, redirect, url_for, session, g, flash, jsonify, send_from_directory
import sqlite3, os
from werkzeug.security import generate_password_hash, check_password_hash
from werkzeug.utils import secure_filename
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent
DB_PATH = BASE_DIR / "fitzone.db"
UPLOADS = BASE_DIR / "uploads"
ALLOWED = {'png','jpg','jpeg','gif'}

app = Flask(__name__)
app.secret_key = "replace-this-with-a-secure-random-key"
app.config['UPLOAD_FOLDER'] = str(UPLOADS)

def get_db():
    conn = sqlite3.connect(DB_PATH)
    conn.row_factory = sqlite3.Row
    return conn

@app.before_request
def load_user():
    g.user = None
    if "user_id" in session:
        db = get_db()
        user = db.execute("SELECT id, email, name FROM users WHERE id = ?", (session["user_id"],)).fetchone()
        g.user = user

@app.route('/uploads/<filename>')
def uploaded_file(filename):
    return send_from_directory(app.config['UPLOAD_FOLDER'], filename)

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/workouts')
def workouts():
    db = get_db()
    plans = db.execute("SELECT * FROM workouts").fetchall()
    return render_template('workouts.html', plans=plans)

@app.route('/nutrition')
def nutrition():
    db = get_db()
    recipes = db.execute("SELECT * FROM recipes").fetchall()
    return render_template('nutrition.html', recipes=recipes)

@app.route('/calculators')
def calculators():
    return render_template('calculators.html')

@app.route('/blog')
def blog():
    db = get_db()
    posts = db.execute("SELECT * FROM blog ORDER BY id DESC").fetchall()
    return render_template('blog.html', posts=posts)

@app.route('/contact', methods=['GET','POST'])
def contact():
    if request.method == 'POST':
        name = request.form.get('name')
        email = request.form.get('email')
        msg = request.form.get('message')
        db = get_db()
        db.execute("INSERT INTO contacts (name,email,message) VALUES (?,?,?)",(name,email,msg))
        db.commit()
        flash('Message sent — we will contact you','success')
        return redirect(url_for('contact'))
    return render_template('contact.html')

@app.route('/register', methods=['GET','POST'])
def register():
    if request.method == 'POST':
        name = request.form.get('name'); email = request.form.get('email'); password = request.form.get('password')
        db = get_db()
        exists = db.execute("SELECT id FROM users WHERE email=?",(email,)).fetchone()
        if exists:
            flash('Email already registered','danger'); return redirect(url_for('register'))
        hashpw = generate_password_hash(password)
        db.execute("INSERT INTO users (name,email,password) VALUES (?,?,?)",(name,email,hashpw))
        db.commit(); flash('Registered — please login','success'); return redirect(url_for('login'))
    return render_template('register.html')

@app.route('/login', methods=['GET','POST'])
def login():
    if request.method == 'POST':
        email = request.form.get('email'); password = request.form.get('password')
        db = get_db(); user = db.execute("SELECT * FROM users WHERE email=?",(email,)).fetchone()
        if user and check_password_hash(user['password'], password):
            session['user_id'] = user['id']; flash('Logged in','success'); return redirect(url_for('profile'))
        flash('Invalid credentials','danger'); return redirect(url_for('login'))
    return render_template('login.html')

@app.route('/logout')
def logout():
    session.clear(); flash('Logged out','info'); return redirect(url_for('index'))

@app.route('/profile', methods=['GET','POST'])
def profile():
    if not g.user: return redirect(url_for('login'))
    db = get_db()
    user = db.execute("SELECT * FROM users WHERE id=?",(g.user['id'],)).fetchone()
    progress = db.execute("SELECT date, weight, note FROM progress WHERE user_id=? ORDER BY date DESC",(g.user['id'],)).fetchall()
    streak = len(progress) if progress else 0
    if request.method == 'POST':
        weight = request.form.get('weight'); height = request.form.get('height'); goal = request.form.get('goal')
        db.execute("UPDATE users SET weight=?, height=?, goal=? WHERE id=?",(weight,height,goal,g.user['id'])); db.commit()
        if 'avatar' in request.files:
            f = request.files['avatar']
            if f and f.filename!='':
                fn = secure_filename(f.filename)
                dest = os.path.join(app.config['UPLOAD_FOLDER'], fn)
                f.save(dest)
                db.execute("UPDATE users SET avatar=? WHERE id=?",(fn,g.user['id'])); db.commit()
        flash('Profile updated','success'); return redirect(url_for('profile'))
    return render_template('profile.html', user=user, progress=progress, streak=streak)

@app.route('/add_progress', methods=['POST'])
def add_progress():
    if not g.user: return redirect(url_for('login'))
    date = request.form.get('date'); weight = request.form.get('weight'); note = request.form.get('note')
    db = get_db(); db.execute("INSERT INTO progress (user_id,date,weight,note) VALUES (?,?,?,?)",(g.user['id'],date,weight,note)); db.commit()
    flash('Progress saved','success'); return redirect(url_for('profile'))

@app.route('/api/calc', methods=['POST'])
def api_calc():
    data = request.get_json(force=True)
    try:
        height = float(data.get('height')); weight = float(data.get('weight')); age = float(data.get('age'))
        sex = data.get('sex','male'); activity = float(data.get('activity',1.55))
        if sex == 'male': bmr = 10*weight + 6.25*height - 5*age + 5
        else: bmr = 10*weight + 6.25*height - 5*age -161
        tdee = round(bmr*activity)
        resp = {'bmr':round(bmr),'tdee':tdee}
        return jsonify(resp)
    except Exception as e:
        return jsonify({'error':str(e)}),400

if __name__ == '__main__':
    os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
    app.run(debug=True)
