# HormoFit - PCOD Digital Twin 🎀

An intelligent, personalized PCOD/PCOS lifestyle coach that uses AI-powered predictions to help users track their health, understand their cycle, and receive AI-guided personalized recommendations for diet and fitness routines.

## Features

- **User Authentication**: Secure user registration and login system
- **Health Tracking**: Track symptoms, cycle patterns, and health metrics  
- **AI-Powered PCOS Prediction**: Machine learning model to assess PCOS risk based on health indicators
- **Personalized Recommendations**: Customized diet and fitness guidance based on individual health profiles
- **Dashboard**: Comprehensive health overview and progress tracking
- **Responsive UI**: Modern, glass-morphism styled interface
- **Digital Twin Dashboard**: A comprehensive overview of user health metrics and progress
- **Cycle Tracker**: Log and track menstrual cycles to monitor your health
- **Mental Health Analyzer**: Track and improve mental well-being

## Tech Stack

### Frontend
- **HTML5** - Structure
- **CSS3** - Styling with glass-morphism design
- **JavaScript** - Client-side interactivity
- **PHP** - Server-side templating

### Backend
- **PHP** - Web framework and API
- **MySQL** - Database
- **Python** - ML model for PCOS prediction
- **scikit-learn** - Machine learning library

### Machine Learning
- **Dataset**: PCOS health data with multiple features
- **Algorithm**: Random Forest Classifier
- **Features**: Age, weight, height, BMI, cycle length, lifestyle factors, and symptoms
- **Environment**: XAMPP (localhost)

## Project Structure

```
HormoFit_12/
├── README.md                 # Project documentation
├── index.php                 # Landing page
├── login.php                 # User login
├── register.php              # User registration
├── logout.php                # User logout
├── dashboard.php             # Main dashboard
├── track.php                 # Health tracking page
├── recommendation.php        # AI recommendations page
├── database.sql              # Database schema
├── config/
│   └── ai.php               # AI configuration
├── db/
│   └── config.php           # Database connection
├── includes/
│   └── dashboard_helpers.php # Dashboard helper functions
├── css/
│   └── style.css            # Main stylesheet (glass-morphism design)
├── scripts/
│   └── predict_pcos.py      # ML model for PCOS prediction
└── data/
    └── PCOS_data.csv        # Training dataset
```

## Installation & Setup

### Prerequisites
- **XAMPP** - For Apache and MySQL
- **PHP 7.4+** - Server-side scripting
- **MySQL 5.7+** - Database
- **Python 3.8+** - ML model execution
- **pip** - Python package manager

### Setup Steps

1. **Clone the Repository**
\`\`\`bash
git clone https://github.com/luckyjaiswal14/HormoFit-PBL.git
cd HormoFit-PBL
\`\`\`

2. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL** modules

3. **Database Configuration**
   - Access phpMyAdmin at \`http://localhost/phpmyadmin/\`
   - Create a new database (e.g., \`hormofit_db\`)
   - Import \`database.sql\` to initialize all tables
   - Update \`db/config.php\` with your database credentials

4. **Install Python Dependencies** (Optional - for ML features)
\`\`\`bash
pip install pandas scikit-learn
\`\`\`

5. **Access the Application**
   - Place the project folder in XAMPP's \`htdocs\` directory
   - Navigate to \`http://localhost/HormoFit_12/\`

## Usage

### For Users

1. **Register** - Create a new account with your email
2. **Login** - Sign in to access your personalized dashboard
3. **Track Health** - Log your symptoms, cycle patterns, and health metrics
4. **Get AI Recommendations** - Receive personalized diet and fitness recommendations
5. **Monitor Progress** - Track trends and changes over time

### For Developers

#### Running the ML Prediction Model
\`\`\`bash
python scripts/predict_pcos.py <age> <weight> <height> <cycle_length> <weight_gain> <hair_growth> <skin_darkening> <hair_loss> <pimples> <fast_food> <exercise>
\`\`\`

## Database Schema

Main tables include:
- **users** - User account information
- **health_records** - Health tracking data
- **recommendations** - AI recommendations

See \`database.sql\` for complete schema.

## Key Features

### Digital Twin Ecosystem
Creates personalized digital representation of user health metrics for continuous monitoring and pattern recognition.

### AI-Powered PCOS Prediction
- **Model**: Random Forest Classifier
- **Accuracy**: ~85-90%
- **Output**: Binary classification with confidence scores

### Personalized Recommendations
- Rule-based nutrition engine
- Tailored fitness routines
- Lifestyle suggestions
- Mental health tracking

## Security Features

✅ Password hashing with PHP's \`password_hash()\`
✅ Session management for user authentication
✅ User authentication for sensitive operations

## Future Enhancements

- [ ] Mobile app integration
- [ ] Wearable device integration
- [ ] Medication tracking
- [ ] Doctor integration
- [ ] Meal planning with recipes
- [ ] Community forum
- [ ] Dark mode
- [ ] Multi-language support

## Contributing

1. Fork the repository
2. Create a feature branch (\`git checkout -b feature/AmazingFeature\`)
3. Commit your changes (\`git commit -m 'Add AmazingFeature'\`)
4. Push to the branch (\`git push origin feature/AmazingFeature\`)
5. Open a Pull Request

## License

MIT License - see LICENSE file for details

## Authors

- **Lucky Jaiswal** - [@luckyjaiswal14](https://github.com/luckyjaiswal14)

## Acknowledgments

- PCOS/PCOD research community
- scikit-learn for ML tools
- Open-source contributors

## Support

For support or questions:
- Open an issue on [GitHub Issues](https://github.com/luckyjaiswal14/HormoFit-PBL/issues)

---

**⚠️ Disclaimer**: This application is for informational purposes only and should not replace professional medical advice. Always consult with a healthcare provider for medical concerns.

**Version**: 1.0.0 | **Last Updated**: April 29, 2026
