# Messenger App

[![Laravel](https://img.shields.io/badge/Laravel-11-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

A real-time messaging application built with Laravel, featuring user authentication, instant messaging, file attachments, and more.

## Features

- **Real-time Messaging**: Instant message delivery using WebSockets and Pusher
- **User Authentication**: Secure login and registration system
- **User Search**: Find and connect with other users
- **Favorites**: Mark users as favorites for quick access
- **File Attachments**: Send images with messages
- **Message Management**: View, mark as seen, and delete messages
- **Contact List**: Manage your messaging contacts
- **Responsive Design**: Mobile-friendly interface with Tailwind CSS
- **Emoji Support**: Enhanced messaging with emoji picker

## Tech Stack

- **Backend**: Laravel 11 (PHP Framework)
- **Frontend**: Alpine.js, Tailwind CSS
- **Database**: MySQL
- **Real-time Communication**: Pusher / Laravel Echo
- **Build Tool**: Vite
- **Authentication**: Laravel Sanctum
- **Notifications**: PHP Flasher

## Prerequisites

Before you begin, ensure you have met the following requirements:

- PHP 8.2 or higher
- Composer
- Node.js and npm
- MySQL or another supported database
- Pusher account (for real-time features)

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/messenger-app.git
   cd messenger-app
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   ```
   Update the `.env` file with your database credentials and Pusher settings.

5. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

6. **Run Database Migrations**
   ```bash
   php artisan migrate
   ```

7. **Build Assets**
   ```bash
   npm run build
   ```

8. **Start the Application**
   ```bash
   php artisan serve
   ```

9. **For Development (with hot reload)**
   ```bash
   npm run dev
   ```

## Usage

1. Register a new account or log in with existing credentials
2. Search for users to start a conversation
3. Click on a user to open the chat interface
4. Type your message and press Enter to send
5. Use the attachment button to send images
6. Mark users as favorites for quick access
7. View your contact list and message history

## Database Schema

The application uses the following main tables:
- `users`: User accounts
- `messages`: Chat messages
- `favorites`: User favorites

## API Endpoints

The application provides RESTful API endpoints for:
- User management
- Message handling
- File uploads
- Real-time broadcasting

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

If you have any questions or need help, please open an issue on GitHub.

---

Built with ❤️ using Laravel
