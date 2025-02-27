# Chat with Laravel Docs

This is a sample application that demonstrates how to use the Upstash Vector database with the OpenAI API to build a chatbot that can answer questions about Laravel.

It ingests the Laravel documentation from the GitHub repository, breaks the documentation
into chunks and stores them in our Vector database.

The chatbot then uses the Vector database to answer questions about the documentation.

## Stack
- Tailwind CSS
- Alpine.js
- Laravel
- Livewire
- Upstash Vector
- OpenAI

## Installation

1. Clone the repository
2. Install dependencies with `composer install`
3. Create an account on Upstash and a Vector index.
4. Copy the `.env.example` file to `.env` and fill in the required environment variables.
5. Run `php artisan app:ingest:documentation` to populate the database with the documentation.

## Usage

1. Run the application with `php artisan serve`
2. Open your browser and navigate to `http://localhost:8000`
