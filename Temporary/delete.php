<?php
if (isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = '../Uploads/' . $filename;
    
    if (file_exists($filepath)) {
        if (unlink($filepath)) {
            header('Location: file_manager.php?success=File deleted successfully');
        } else {
            header('Location: file_manager.php?error=Failed to delete file');
        }
    } else {
        header('Location: file_manager.php?error=File not found');
    }
} else {
    header('Location: file_manager.php?error=No file specified');
}
exit;
?>
