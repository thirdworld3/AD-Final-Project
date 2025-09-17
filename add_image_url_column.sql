-- Add image_url column to products table for image upload functionality
-- Run this migration to add support for product images

USE forbidden_codex;

ALTER TABLE products 
ADD COLUMN image_url VARCHAR(500) DEFAULT NULL AFTER description;

-- Update existing products with placeholder image paths if needed
-- UPDATE products SET image_url = 'assets/images/placeholder-product.jpg' WHERE image_url IS NULL;
