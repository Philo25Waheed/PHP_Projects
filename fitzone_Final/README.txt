
FitZone v8 - Neon Cyberpunk
===========================
This project is a local multi-page static site optimized for Visual Studio / Live Server.
Files added/modified:
- index.html, about.html, services.html, meals.html, contact.html, login.html
- css/style.css  (neon cyberpunk styles)
- js/main.js     (auth demo, calorie calculator, streak tracking, video modal)
- img/hero_v2.jpeg (hero image)
- img/meal_1..meal_10.jpg (placeholder meal images)

How to use:
1. Open the project folder in Visual Studio Code.
2. Start a static server (Live Server extension) or open index.html in a browser.
3. Login is demo-only and stored in localStorage. Use any email/password to log in.
4. Mark workouts via "سجّل تمرين جديد" to update streak (stored in localStorage). If a new workout is logged after 24h since last, streak increments.

Notes:
- You can replace placeholder meal images in img/ with real photos (keep same filenames).
- For production you'd replace localStorage auth with real backend.
