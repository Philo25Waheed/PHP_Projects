/**
 * =====================================================
 * FitZone Main JavaScript
 * =====================================================
 * Handles authentication, UI updates, and API calls
 */

const App = {
  // API base URL
  apiBase: 'api/',

  // Current user data
  user: null,

  /**
   * Initialize the application
   */
  init() {
    this.cache();
    this.bind();
    this.checkAuth();
    this.updateStreakUI();
  },

  /**
   * Cache DOM elements
   */
  cache() {
    this.body = document.body;
    this.loginForm = document.getElementById('loginForm');
    this.logoutBtn = document.getElementById('logoutBtn');
    this.calForm = document.getElementById('calForm');
    this.streakElem = document.getElementById('streakCount');
    this.exerciseList = document.getElementById('exerciseList');
    this.contactForm = document.querySelector('.panel form');
  },

  /**
   * Bind event listeners
   */
  bind() {
    if (this.loginForm) {
      this.loginForm.addEventListener('submit', e => {
        e.preventDefault();
        this.login();
      });
    }

    if (this.logoutBtn) {
      this.logoutBtn.addEventListener('click', () => this.logout());
    }

    if (this.calForm) {
      this.calForm.addEventListener('submit', e => {
        e.preventDefault();
        this.calcCalories();
      });
    }

    // Bind contact form if on contact page
    if (this.contactForm && window.location.pathname.includes('contact')) {
      this.contactForm.addEventListener('submit', e => {
        e.preventDefault();
        this.submitContact();
      });
    }

    this.setupMobileNav();
    this.renderExercises();
  },

  /**
   * Mobile Navigation Toggle
   */
  setupMobileNav() {
    const hamburger = document.getElementById('navToggle');
    const nav = document.querySelector('.nav');

    if (hamburger && nav) {
      hamburger.addEventListener('click', () => {
        nav.classList.toggle('active');

        // Optional: Animate hamburger bars
        const bars = hamburger.querySelectorAll('.bar');
        if (nav.classList.contains('active')) {
          bars[0].style.transform = 'rotate(45deg) translate(5px, 6px)';
          bars[1].style.opacity = '0';
          bars[2].style.transform = 'rotate(-45deg) translate(5px, -6px)';
        } else {
          bars[0].style.transform = 'none';
          bars[1].style.opacity = '1';
          bars[2].style.transform = 'none';
        }
      });
    }
  },

  /**
   * Check authentication status with server
   */
  async checkAuth() {
    try {
      const response = await fetch(this.apiBase + 'auth/check.php', {
        credentials: 'include'
      });
      const data = await response.json();

      if (data.authenticated && data.user) {
        this.user = data.user;
        localStorage.setItem('fitzone_user', JSON.stringify(this.user));
      } else {
        this.user = null;
        localStorage.removeItem('fitzone_user');
      }
    } catch (error) {
      console.log('Auth check failed, using local storage');
      // Fallback to localStorage
      this.user = JSON.parse(localStorage.getItem('fitzone_user') || 'null');
    }

    this.updateAuthUI();
    this.updateStreakUI();
  },

  /**
   * Login user
   */
  async login() {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    const email = emailInput.value.trim();
    const password = passwordInput.value;

    if (!email || !password) {
      alert('Please enter your email and password');
      return;
    }

    try {
      const response = await fetch(this.apiBase + 'auth/login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify({ email, password })
      });

      const data = await response.json();

      if (data.success && data.user) {
        this.user = data.user;
        localStorage.setItem('fitzone_user', JSON.stringify(this.user));
        this.updateAuthUI();
        alert('Login successful! Welcome back, ' + this.user.name);
        window.location.href = 'index.html';
      } else {
        alert(data.error || 'Login failed. Please check your credentials.');
      }
    } catch (error) {
      console.error('Login error:', error);
      alert('Connection error. Please try again later.');
    }
  },

  /**
   * Logout user
   */
  async logout() {
    try {
      await fetch(this.apiBase + 'auth/logout.php', {
        method: 'POST',
        credentials: 'include'
      });
    } catch (error) {
      console.log('Logout API call failed');
    }

    // Clear local data
    this.user = null;
    localStorage.removeItem('fitzone_user');
    localStorage.removeItem('streak');
    localStorage.removeItem('lastWorkout');

    this.updateAuthUI();
    alert('Logged out successfully');
    window.location.href = 'index.html';
  },

  /**
   * Update UI based on authentication status
   */
  updateAuthUI() {
    if (this.user) {
      document.querySelectorAll('.auth-only').forEach(el => el.style.display = 'inline-block');
      document.querySelectorAll('.guest-only').forEach(el => el.style.display = 'none');

      const emailEl = document.getElementById('userEmail');
      if (emailEl) {
        emailEl.textContent = this.user.email || this.user.name;
      }
    } else {
      document.querySelectorAll('.auth-only').forEach(el => el.style.display = 'none');
      document.querySelectorAll('.guest-only').forEach(el => el.style.display = 'inline-block');
    }
  },

  /**
   * Calculate calories using Mifflin-St Jeor equation
   */
  calcCalories() {
    const weight = parseFloat(document.getElementById('weight').value);
    const height = parseFloat(document.getElementById('height').value);
    const age = parseInt(document.getElementById('age').value);
    const gender = document.getElementById('gender').value;

    if (!weight || !height || !age) {
      alert('Please enter all values');
      return;
    }

    // Mifflin-St Jeor Equation
    let bmr = gender === 'male'
      ? 10 * weight + 6.25 * height - 5 * age + 5
      : 10 * weight + 6.25 * height - 5 * age - 161;

    const activity = document.getElementById('activity').value;
    const factor = parseFloat(activity);
    const calories = Math.round(bmr * factor);

    document.getElementById('calResult').textContent = calories + ' kcal/day';
  },

  /**
   * Render exercises from API or fallback
   */
  async renderExercises() {
    if (!this.exerciseList) return;

    let exercises = [
      { title: 'Push Ups', video: 'https://www.youtube.com/embed/_l3ySVKYVJ8' },
      { title: 'Squats', video: 'https://www.youtube.com/embed/aclHkVaku9U' },
      { title: 'Plank', video: 'https://www.youtube.com/embed/pSHjTRCQxIw' },
      { title: 'Deadlift', video: 'https://www.youtube.com/embed/op9kVnSso6Q' },
      { title: 'Shoulder Press', video: 'https://www.youtube.com/embed/qEwKCR5JCog' }
    ];

    // Try to fetch from API
    try {
      const response = await fetch(this.apiBase + 'exercises/list.php');
      const data = await response.json();

      if (data.success && data.exercises) {
        exercises = data.exercises.map(ex => ({
          title: ex.title,
          video: ex.video_url,
          description: ex.description
        }));
      }
    } catch (error) {
      console.log('Using fallback exercises');
    }

    // Clear existing content
    this.exerciseList.innerHTML = '';

    exercises.forEach(ex => {
      const div = document.createElement('div');
      div.className = 'card';
      div.innerHTML = `
        <h3>${ex.title}</h3>
        <p>${ex.description || 'Exercise explained by a professional trainer.'}</p>
        <div style="margin-top:8px">
          <button class="btn" onclick="App.openVideo('${ex.video}')">Watch the Video</button>
        </div>
      `;
      this.exerciseList.appendChild(div);
    });
  },

  /**
   * Open video modal
   */
  openVideo(src) {
    const modal = document.getElementById('videoModal');
    if (!modal) return;

    modal.innerHTML = `
      <div style="position:relative;padding-top:56.25%">
        <iframe src="${src}" style="position:absolute;left:0;top:0;width:100%;height:100%" frameborder="0" allowfullscreen></iframe>
      </div>
      <div style="text-align:right;margin-top:8px">
        <button class="btn" onclick="App.closeVideo()">Close</button>
      </div>
    `;
    modal.style.display = 'block';
  },

  /**
   * Close video modal
   */
  closeVideo() {
    const modal = document.getElementById('videoModal');
    if (modal) {
      modal.style.display = 'none';
      modal.innerHTML = '';
    }
  },

  /**
   * Mark workout as done and update streak
   */
  async markWorkoutDone() {
    if (!this.user) {
      // For non-logged-in users, use localStorage
      const now = Date.now();
      const last = parseInt(localStorage.getItem('lastWorkout') || '0');
      let streak = parseInt(localStorage.getItem('streak') || '0');

      if (!last) {
        streak = 1;
      } else {
        const diff = now - last;
        if (diff > 24 * 60 * 60 * 1000) {
          streak += 1;
        }
      }

      localStorage.setItem('lastWorkout', now.toString());
      localStorage.setItem('streak', streak.toString());
      this.updateStreakUI();
      alert('Workout logged! Excitement Days: ' + streak);
      return;
    }

    // For logged-in users, use API
    try {
      const response = await fetch(this.apiBase + 'progress/add.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify({
          workout_completed: true,
          date: new Date().toISOString().split('T')[0]
        })
      });

      const data = await response.json();

      if (data.success) {
        // Update local streak
        if (this.user) {
          this.user.streak = data.streak;
          localStorage.setItem('fitzone_user', JSON.stringify(this.user));
        }
        localStorage.setItem('streak', data.streak.toString());
        this.updateStreakUI();
        alert('Workout logged! Excitement Days: ' + data.streak);
      } else {
        alert(data.error || 'Failed to log workout');
      }
    } catch (error) {
      console.error('Error logging workout:', error);
      alert('Connection error. Try again later.');
    }
  },

  /**
   * Update streak display in UI
   */
  updateStreakUI() {
    let streak = 0;

    if (this.user && this.user.streak) {
      streak = this.user.streak;
    } else {
      streak = parseInt(localStorage.getItem('streak') || '0');
    }

    if (this.streakElem) {
      this.streakElem.textContent = streak;
    }
  },

  /**
   * Submit contact form
   */
  async submitContact() {
    const form = this.contactForm;
    const inputs = form.querySelectorAll('input, textarea');

    const name = inputs[0]?.value?.trim();
    const email = inputs[1]?.value?.trim();
    const message = inputs[2]?.value?.trim();

    if (!name || !email || !message) {
      alert('Please fill in all fields');
      return;
    }

    try {
      const response = await fetch(this.apiBase + 'contact/submit.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name, email, message })
      });

      const data = await response.json();

      if (data.success) {
        alert('Message sent successfully! We will contact you soon.');
        form.reset();
      } else {
        alert(data.error || 'Failed to send message');
      }
    } catch (error) {
      console.error('Contact error:', error);
      alert('Connection error. Please try again.');
    }
  }
};

// Advanced calorie & macro calculator
App.calcAdvanced = function () {
  const w = parseFloat(document.getElementById('adv_weight').value);
  const h = parseFloat(document.getElementById('adv_height').value);
  const age = parseInt(document.getElementById('adv_age').value);
  const gender = document.getElementById('adv_gender').value;
  const activity = parseFloat(document.getElementById('adv_activity').value);
  const goal = document.getElementById('adv_goal').value;

  if (!w || !h || !age) {
    alert('Please enter the values');
    return;
  }

  let bmr = gender === 'male'
    ? 10 * w + 6.25 * h - 5 * age + 5
    : 10 * w + 6.25 * h - 5 * age - 161;

  let maintenance = Math.round(bmr * activity);
  let calories = maintenance;

  if (goal === 'bulking') calories = Math.round(maintenance * 1.15);
  if (goal === 'cutting') calories = Math.round(maintenance * 0.8);

  const macros = {
    bulking: { protein: 0.30, carbs: 0.50, fat: 0.20 },
    cutting: { protein: 0.40, carbs: 0.30, fat: 0.30 },
    maintenance: { protein: 0.35, carbs: 0.45, fat: 0.20 }
  };

  const m = macros[goal];
  const protein_g = Math.round((calories * m.protein) / 4);
  const carbs_g = Math.round((calories * m.carbs) / 4);
  const fat_g = Math.round((calories * m.fat) / 9);

  const resultsDiv = document.getElementById('advResults');
  resultsDiv.innerHTML = `
    <div class="result-card"><h4>Calories</h4><p>${calories} kcal/day</p></div>
    <div class="result-card"><h4>Protein</h4><p>${protein_g} g</p></div>
    <div class="result-card"><h4>Carbs</h4><p>${carbs_g} g</p></div>
    <div class="result-card"><h4>Fat</h4><p>${fat_g} g</p></div>
  `;
};

// Split modal content
App.openSplit = function (key) {
  const content = document.getElementById('splitContent');
  const map = {
    bro: {
      title: 'Bro Split',
      body: `<h3>Bro Split</h3><p>Saturday: Chest<br>Sunday: Back<br>Monday: Shoulders<br>Tuesday: Legs<br>Wednesday: Arms<br>Thursday/Friday: Rest</p><p>Multiple isolation exercises · High number of sets · Ideal for bulking</p>`
    },
    full: {
      title: 'Full Body',
      body: `<h3>Full Body</h3><p>Full body in the same day · 3–4 times per week</p><p>Basic compound exercises · Focus on technique and strength</p>`
    },
    pushpull: {
      title: 'Push / Pull',
      body: `<h3>Push / Pull</h3><p>Push: Chest + Shoulders + Triceps · Pull: Back + Biceps</p><p>Compound exercises: 70%</p>`
    },
    bodypart: {
      title: 'Body Part Split',
      body: `<h3>Body Part Split</h3><p>Day 1: Major muscle groups (Chest + Back + Legs)<br>Day 2: Minor muscle groups (Shoulders + Biceps + Triceps)</p><p>Supersets · Drop sets · Suitable for 3–4 days per week</p>`
    },
    power: {
      title: 'Powerbuilding',
      body: `<h3>Powerbuilding</h3><p>Combination of Strength + Hypertrophy · 4–5 days per week</p><p>Heavy compound exercises + light isolation exercises · Low reps on strength days (3–5) and high reps on hypertrophy days (8–12)</p>`
    }
  };

  const d = map[key];
  content.innerHTML = `
    <div style="display:flex;gap:12px">
      <div style="flex:1">
        <h2 style="color:var(--neon)">${d.title}</h2>
        ${d.body}
      </div>
      <div style="width:220px">
        <button class="btn" onclick="App.closeSplit()">Close</button>
      </div>
    </div>
  `;
  document.getElementById('splitModal').style.display = 'flex';
};

App.closeSplit = function () {
  document.getElementById('splitModal').style.display = 'none';
  document.getElementById('splitContent').innerHTML = '';
};

// Modal backdrop close handling
document.addEventListener('click', function (e) {
  const backdrop = document.getElementById('splitModal');
  if (backdrop && backdrop.style.display === 'flex' && e.target === backdrop) {
    App.closeSplit();
  }
});

document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    App.closeSplit();
  }
});

// Reveal animations
document.addEventListener('DOMContentLoaded', function () {
  setTimeout(function () {
    document.querySelectorAll('.fade-slide').forEach(function (el) {
      el.classList.add('show');
    });
  }, 120);
});

// Initialize app on DOM load
window.addEventListener('DOMContentLoaded', () => App.init());
