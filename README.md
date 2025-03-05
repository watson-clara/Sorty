# Sorty: File Comparison Tool

![PHP Version](https://img.shields.io/badge/PHP-8.1-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Build Status](https://img.shields.io/badge/build-passing-brightgreen)

A high-performance tool for comparing lexicographically sorted text files to identify unique lines in each file.

## Features

- **Efficient Comparison**: O(n) algorithm for optimal performance
- **Dual Interfaces**: Both web and command-line interfaces
- **Docker Support**: Easy deployment with Docker
- **Responsive Design**: Works on desktop and mobile devices
- **Dark Mode**: User-selectable theme preference
- **Drag & Drop**: Modern file upload experience


## Installation

### Using Docker

```bash
# Clone the repository
git clone https://github.com/watson-clara/sorty.git
cd sorty

# Start the Docker container
docker-compose up -d
```

### Manual Installation

```bash
# Clone the repository
git clone https://github.com/watson-clara/sorty.git
cd sorty

# Install dependencies
composer install

# Set permissions
chmod 755 run.php
mkdir -p logs
chmod 755 logs
```

## Usage

### Web Interface
```bash
# Start the local server:
php -S localhost:8080
```
1. Access `http://localhost:8080` in your browser 
2. Upload two sorted text files
3. View and download the comparison results

### Command Line

```bash
# Basic usage
php run.php input1.txt input2.txt output1.txt output2.txt

# Example with real files
php run.php tests/TestFiles/File1.txt tests/TestFiles/File2.txt results/unique1.txt results/unique2.txt
```

## Development

### Running Tests

The project includes a comprehensive test suite using PHPUnit:

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite=Comparers

# Run a specific test file
vendor/bin/phpunit tests/Comparers/LineByLineComparerTest.php

# Run with coverage report (requires Xdebug)
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html coverage
```

### Manual Testing

For manual testing of the web interface:

```bash
# Start the PHP development server
php -S localhost:8080 -t public/

# Access in your browser
# http://localhost:8080
```

### Test Files

Sample test files are provided in the `tests/TestFiles/` directory:

- `File1.txt` and `File2.txt`: Small test files for basic testing
- `Large1.txt` and `Large2.txt`: Larger files for performance testing

## Performance

The comparison algorithm is optimized for sorted input:
- **Time Complexity**: O(n) where n is the total number of lines
- **Space Complexity**: O(n) for storing unique lines
- **Memory Usage**: Efficient stream-based processing for large files

## Code Style

The project follows PSR-12 coding standards.
