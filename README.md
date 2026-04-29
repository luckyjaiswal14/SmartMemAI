# HormoFit

HormoFit is a comprehensive, AI-powered PCOD (Polycystic Ovary Syndrome) Digital Twin ecosystem and management system. It aims to provide personalized guidance, health tracking, and wellness routines for individuals managing PCOD.

## Features
- **Digital Twin Dashboard**: A comprehensive overview of user health metrics and progress.
- **Cycle Tracker**: Log and track menstrual cycles to monitor your health intuitively.
- **Nutrition Engine**: Provide personalized, rule-based dietary recommendations tailored for individuals with PCOD.
- **Mental Health Analyzer**: Track and improve mental well-being over time through targeted assessments.
- **User Authentication**: Secure signup and login system to provide a truly personalized experience.

## Technology Stack
- **Frontend**: HTML5, Vanilla CSS, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **Environment**: Developed and tested on XAMPP (localhost environment)

## Setup and Installation

1. **Prerequisites**: Ensure you have [XAMPP](https://www.apachefriends.org/index.html) installed on your system.
2. **Clone the Repository**:
   ```bash
   git clone https://github.com/luckyjaiswal14/HormoFit-PBL.git
   ```
3. **Database Configuration**:
   - Open the XAMPP Control Panel and start the **Apache** and **MySQL** modules.
   - Access **phpMyAdmin** in your browser at `http://localhost/phpmyadmin/`.
   - Create a new database appropriate for HormoFit (e.g., `hormofit_db`).
   - Import the `database.sql` file provided in the repository to initialize all tables.
   - Update `db/config.php` with your local database credentials if they differ from the defaults.
4. **Running the Application**:
   - Move or copy the project folder into your XAMPP installation directory under `htdocs` (e.g., `C:\xampp\htdocs\HormoFit-PBL` or `/Applications/XAMPP/xamppfiles/htdocs/HormoFit-PBL`).
   - Navigate to `http://localhost/HormoFit-PBL` in your web browser.

## Contributing
Feel free to open an issue or submit a pull request for improvements and bug fixes.
