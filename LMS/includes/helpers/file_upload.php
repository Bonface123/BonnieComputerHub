<?php
/**
 * File Upload Helper Functions
 * Handles thumbnail and document uploads for the LMS
 */

/**
 * Upload an image file and return the filename
 * 
 * @param array $file The $_FILES array element
 * @param string $destination The destination directory
 * @param array $allowed_types Allowed mime types
 * @param int $max_size Maximum file size in bytes
 * @return array Result with status and filename/error
 */
function upload_image($file, $destination = '../uploads/course_thumbnails/', $allowed_types = ['image/jpeg', 'image/png', 'image/webp'], $max_size = 5242880) {
    // Create directory if it doesn't exist
    if (!file_exists($destination)) {
        mkdir($destination, 0777, true);
    }
    
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        return ['status' => false, 'error' => $errors[$file['error']] ?? 'Unknown upload error'];
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        return ['status' => false, 'error' => 'File size exceeds the maximum limit (' . ($max_size / 1024 / 1024) . 'MB)'];
    }
    
    // Check file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $file_type = $finfo->file($file['tmp_name']);
    if (!in_array($file_type, $allowed_types)) {
        return ['status' => false, 'error' => 'Invalid file type. Allowed types: ' . implode(', ', $allowed_types)];
    }
    
    // Generate a unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = $destination . $new_filename;
    
    // Move the file to the destination directory
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['status' => false, 'error' => 'Failed to move uploaded file'];
    }
    
    return ['status' => true, 'filename' => $new_filename];
}

/**
 * Delete a file from the server
 * 
 * @param string $filename The filename to delete
 * @param string $directory The directory containing the file
 * @return bool Whether the deletion was successful
 */
function delete_file($filename, $directory = '../uploads/course_thumbnails/') {
    $filepath = $directory . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}
