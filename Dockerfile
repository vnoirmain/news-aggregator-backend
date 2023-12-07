# Dockerfile

# Use the official Sail image as the base image
FROM sail-8.2/app:latest

# Set the working directory
WORKDIR /var/www/html

# Copy the entire Laravel application to the container
COPY . .

# Run artisan commands after the container is up
CMD php artisan key:generate && php artisan migrate && php artisan serve --host=0.0.0.0 --port=80
