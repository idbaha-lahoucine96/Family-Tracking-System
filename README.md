# نظام تتبع العائلات  (Family Tracking System)

A web-based geographical tracking system designed specifically for managing and monitoring families in the Guelmim-Oued Noun region of Morocco. The system provides an interactive map interface with family locations, demographic information, and real-time tracking capabilities.

## 🌟 Features

- Interactive map visualization using Leaflet.js
- Real-time family location tracking
- Family information management system
- Photo upload capabilities
- Mobile-responsive design
- Arabic language interface
- Multiple location selection methods:
  - City selection
  - Manual map placement
  - Current location detection
- Region-specific boundaries (Guelmim-Oued Noun)
- Statistical dashboard
- Google Maps navigation integration

## 🔧 Technical Requirements

- PHP 7.0 or higher
- MySQL/MariaDB database
- Web server (Apache/Nginx)
- Modern web browser with JavaScript enabled
- SSL certificate (recommended for geolocation features)

## 📦 Dependencies

### Frontend
- Bootstrap 5.3.0
- Bootstrap Icons 1.10.0
- Leaflet.js 1.7.1
- Custom CSS with CSS Variables
- Modern JavaScript (ES6+)

### Backend
- PHP with PDO extension
- File upload handling
- JSON response formatting

## 🚀 Installation

1. Clone the repository:
```bash
git clone https://gitlab.com/idbahalahoucine2/systeme-de-suivi-des-familles.git
```

2. Set up the database:
- Create a new MySQL database
- Import the provided SQL schema (see database.sql)
- Configure database connection in `database.php`

3. Configure the web server:
- Ensure the `uploads/` directory has write permissions
- Set document root to project directory
- Enable PHP file upload settings in php.ini

4. Configure environment:
```php
// database.php configuration example
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

## 🗺️ Usage

### Adding a New Family
1. Click "إضافة عائلة" (Add Family) button
2. Upload a family photo
3. Enter family name and phone number
4. Choose location method:
   - Select city from dropdown
   - Mark location manually on map
   - Use current location
5. Save the information

### Viewing Family Information
- Click on family markers on the map
- View family details in popup
- Use tracking button for navigation

### Statistics Dashboard
- Total number of families
- Total number of registered cities
- Region coverage information

## 🔒 Security Considerations

- Implement proper input validation
- Sanitize file uploads
- Use prepared SQL statements
- Enable HTTPS for secure geolocation
- Validate coordinate boundaries
- Implement user authentication (if required)

## 📱 Mobile Compatibility

The system is fully responsive and supports:
- Touch interactions
- Mobile geolocation
- Responsive layout adaptation
- Mobile-friendly forms
- Optimized image loading

## 🌐 Region Boundaries

The system is configured for the Guelmim-Oued Noun region with the following coordinates:
- North: 29.8°
- South: 28.2°
- East: -9.2°
- West: -11.2°

## 🛠️ Customization

### Adding New Cities
Modify the city options in the HTML:
```javascript
document.getElementById('citySelect').innerHTML = `
    <option value="newCity" data-coords="lat,lon">City Name</option>
    // Add more cities as needed
`;
```

### Styling
Customize the appearance using CSS variables in the `:root` selector:
```css
:root {
    --primary-color: #your-color;
    --secondary-color: #your-color;
    // Add more custom variables
}
```

## 📞 Support
idbahalahoucinelahoucine96@gmail.com