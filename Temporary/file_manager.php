<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager - HireSwift</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e9ecef;
        }

        .btn {
            padding: 8px 16px;
            background: #4285f4;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn:hover {
            background: #3367d6;
        }

        .files-table {
            width: 100%;
            border-collapse: collapse;
        }

        .files-table th,
        .files-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .files-table th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .file-actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-folder"></i> Uploaded Files</h1>
            <a href="resume_upload.html" class="btn">
                <i class="fas fa-upload"></i> Upload New File
            </a>
        </div>

        <table class="files-table">
            <thead>
                <tr>
                    <th>Filename</th>
                    <th>Size</th>
                    <th>Upload Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $uploadDir = '../Uploads/';
                if (is_dir($uploadDir)) {
                    $files = array_diff(scandir($uploadDir), array('.', '..'));
                    
                    if (empty($files)) {
                        echo '<tr><td colspan="4" style="text-align: center; color: #6c757d;">No files uploaded yet</td></tr>';
                    } else {
                        foreach ($files as $file) {
                            $filePath = $uploadDir . $file;
                            if (is_file($filePath)) {
                                $size = filesize($filePath);
                                $date = date('Y-m-d H:i:s', filemtime($filePath));
                                $sizeFormatted = formatBytes($size);
                                
                                echo "<tr>";
                                echo "<td><i class='fas fa-file-pdf' style='color: #dc3545; margin-right: 8px;'></i>$file</td>";
                                echo "<td>$sizeFormatted</td>";
                                echo "<td>$date</td>";
                                echo "<td class='file-actions'>";
                                echo "<a href='download.php?file=" . urlencode($file) . "' class='btn-sm btn-info'><i class='fas fa-download'></i> Download</a>";
                                echo "<a href='delete.php?file=" . urlencode($file) . "' class='btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")' ><i class='fas fa-trash'></i> Delete</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        }
                    }
                } else {
                    echo '<tr><td colspan="4" style="text-align: center; color: #6c757d;">Uploads directory not found</td></tr>';
                }

                function formatBytes($size, $precision = 2) {
                    $base = log($size, 1024);
                    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
                    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
