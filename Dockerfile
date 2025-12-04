# Use a standard base image with Apache and PHP pre-installed
FROM php:8.2-apache

# Install any necessary extensions (none needed for your basic script)
# RUN docker-php-ext_install ...

# Copy your source code into the document root
COPY . /var/www/html/

# Ensure the DirectoryIndex is set to index.php (Apache's default)
# and the container listens on the correct port (80)
