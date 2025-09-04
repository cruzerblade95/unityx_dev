# Marketplace Screenshot Verification Feature

## Overview
The marketplace now requires sellers to upload a verification screenshot before they can approve transactions. This adds an extra layer of security and proof for all marketplace transactions.

## How It Works

### For Sellers
1. **When a buyer starts verification**: The seller receives a verification request in their "Verification Requests for Your Listings" section
2. **Screenshot Upload Required**: Before the seller can approve the transaction, they must upload a screenshot as proof
3. **Upload Process**: 
   - Click "Upload Screenshot" button
   - Select an image file (JPEG, PNG, JPG - max 2MB)
   - Click upload to submit
4. **After Upload**: 
   - The "Approve" button becomes enabled and the seller can approve the transaction
   - Seller can view the uploaded screenshot
   - **Replace Option**: Seller can replace the screenshot if needed by clicking "Replace" button
5. **Screenshot Management**:
   - **View**: Click "View Screenshot" to preview the uploaded image
   - **Replace**: Click "Replace" to upload a new screenshot (replaces the previous one)
   - **Confirmation**: System asks for confirmation before replacing screenshots
6. **Screenshot Storage**: Screenshots are stored in `storage/app/public/marketplace/screenshots/`

### For Buyers
- Buyers can see if a screenshot has been uploaded by the seller
- The verification process remains the same from buyer's perspective

### For Admins
- **Marketplace List**: New "Screenshot" column shows screenshot status and view button
- **Transaction Detail**: Timeline includes screenshot upload event with view option
- **Screenshot Preview**: Click "View Screenshot" to see uploaded verification proof in a modal

## Database Changes
- Added `verification_screenshot` column to store file path
- Added `screenshot_uploaded_at` timestamp for tracking upload time

## API Endpoints
- `POST /user/marketplace/upload-screenshot/{listingId}` - Upload verification screenshot

## File Storage
- Screenshots are stored in `storage/app/public/marketplace/screenshots/`
- Accessible via public URL through storage link
- Old screenshots are automatically deleted when new ones are uploaded

## Security Features
- File type validation (only images allowed)
- File size limit (2MB max)
- Screenshots required before transaction approval
- Screenshots visible to admins for verification

## User Interface
- **Initial Upload**: Upload form appears in verification requests section when no screenshot exists
- **Post-Upload Interface**: 
  - Success alert shows upload confirmation with timestamp
  - "View Screenshot" button for preview in modal
  - "Replace" button to upload a new screenshot
- **Replace Functionality**:
  - Hidden replace form appears when "Replace" is clicked
  - Confirmation dialog before replacing existing screenshot
  - Cancel option to hide replace form without changes
- **Progress Indicators**: Loading states for upload and replace operations
- **Screenshot Preview**: Modal windows for viewing uploaded images
- **Button States**: Approve button disabled until screenshot is uploaded

## Error Handling
- File validation errors
- Upload failure handling
- Verification expiry checks
- Graceful fallbacks for missing screenshots

This feature ensures all marketplace transactions have proper verification documentation for transparency and security.
