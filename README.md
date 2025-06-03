# AI Chat Assistant

A modern chat application with Gemini AI integration, featuring user authentication, OTP verification, and a beautiful UI.

## Features

- Modern, responsive UI using Tailwind CSS and Bootstrap
- User authentication with email/password
- OTP verification using Google SMTP
- Real-time chat interface
- Integration with Google's Gemini AI
- Chat history storage
- Secure password hashing
- Session management

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- XAMPP (or similar local development environment)
- Google Cloud account with Gemini API access
- Gmail account for SMTP

## Setup Instructions

1. Clone the repository to your XAMPP htdocs directory:
   ```bash
   git clone <repository-url> /path/to/xampp/htdocs/AR\ Bot
   ```

2. Install dependencies using Composer:
   ```bash
   composer install
   ```

3. Create the database and tables:
   - Open phpMyAdmin
   - Import the `database.sql` file

4. Configure the application:
   - Update the database credentials in `config/database.php`
   - Set your Gmail SMTP credentials in `signup.php`
   - Add your Gemini API key in `api/chat.php`

5. Configure Gmail for SMTP:
   - Enable 2-factor authentication in your Gmail account
   - Generate an App Password for the application
   - Use this App Password in the SMTP configuration

6. Start your XAMPP server:
   - Start Apache
   - Start MySQL

7. Access the application:
   - Open your browser and navigate to `http://localhost/AR%20Bot`

## Security Notes

- Never commit sensitive information like API keys or database credentials
- Use environment variables for sensitive data in production
- Keep your dependencies updated
- Implement rate limiting for API endpoints
- Use HTTPS in production

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details. 


apiKey = 'b60015e9046ef4afb65133b77adfe1d1'; // Your API key
apiUrl = `https://api.openweathermap.org/data/2.5/weather?
"# AR-GPT" 
