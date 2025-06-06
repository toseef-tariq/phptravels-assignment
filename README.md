# PHPTRAVELS Technical Assignment - Hotel Data Scraper

## Overview
This repository contains the solution for the PHPTRAVELS Technical Assignment. The `hotel_scraper.php` script integrates with a RapidAPI Booking.com scraper to fetch hotel data for Dubai, formats the data into a standardized JSON structure, and handles errors as per the task requirements.

### Features
- Fetches hotel data for Dubai from a RapidAPI Booking.com scraper.
- Uses static parameters:
  - Location: Dubai
  - Check-in Date: 2025-07-01
  - Check-out Date: 2025-07-05
  - Guests: 2 adults, 1 room
- Formats API response into a JSON structure with fields like `hotel_id`, `name`, `actual_price`, `markup_price`, etc.
- Implements error handling for API failures (e.g., cURL errors, missing data).
- Outputs JSON with prices rounded to 2 decimal places and type-safe fields.

### Requirements
- PHP 7.4 or higher
- cURL extension enabled
- RapidAPI account with access to a Booking.com scraper API (e.g., `booking-com15.p.rapidapi.com`)

### Setup and Usage
1. **Clone the Repository**:
   ```bash
   git clone https://github.com/toseef-tariq/phptravels-assignment.git
   cd phptravels-assignment
