<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Upload Test - HireSwift</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .upload-container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        .header {
            text-align: center;
            margin-bottom: 32px;
        }

        .header h1 {
            color: #212529;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .header p {
            color: #6c757d;
            font-size: 16px;
        }

        .upload-form {
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-display {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-input-display:hover {
            border-color: #4285f4;
            background: #f0f7ff;
        }

        .file-input-display.has-file {
            border-color: #28a745;
            background: #f0fff4;
        }

        .file-icon {
            font-size: 48px;
            color: #6c757d;
            margin-bottom: 16px;
        }

        .file-text {
            text-align: center;
        }

        .file-text h3 {
            color: #495057;
            margin-bottom: 8px;
        }

        .file-text p {
            color: #6c757d;
            font-size: 14px;
        }

        .selected-file {
            margin-top: 16px;
            padding: 12px;
            background: #e7f3ff;
            border-radius: 8px;
            display: none;
        }

        .selected-file.show {
            display: block;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .file-info i {
            color: #4285f4;
            font-size: 20px;
        }

        .file-details h4 {
            color: #212529;
            margin-bottom: 4px;
        }

        .file-details p {
            color: #6c757d;
            font-size: 12px;
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: #4285f4;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #3367d6;
        }

        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        .results {
            margin-top: 32px;
            padding: 24px;
            background: #f8f9fa;
            border-radius: 12px;
            display: none;
        }

        .results.show {
            display: block;
        }

        .results h3 {
            color: #212529;
            margin-bottom: 16px;
        }

        .result-item {
            margin-bottom: 16px;
            padding: 16px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #4285f4;
        }

        .result-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .result-value {
            color: #212529;
        }

        .loading {
            text-align: center;
            padding: 40px;
            display: none;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4285f4;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
</head>
<body>
    <div class="upload-container">
        <div class="header">
            <h1>PDF Resume Upload Test</h1>
            <p>Upload a PDF resume to test the NLP processing</p>
        </div>

        <form id="uploadForm" class="upload-form" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label">Select PDF Resume</label>
                <div class="file-input-wrapper">
                    <input type="file" id="pdfFile" name="pdf_file" class="file-input" accept=".pdf" required>
                    <div class="file-input-display" id="fileDisplay">
                        <div class="file-text">
                            <div class="file-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <h3>Click to upload or drag and drop</h3>
                            <p>PDF files only (Max 5MB)</p>
                        </div>
                    </div>
                </div>
                <div class="selected-file" id="selectedFile">
                    <div class="file-info">
                        <i class="fas fa-file-pdf"></i>
                        <div class="file-details">
                            <h4 id="fileName"></h4>
                            <p id="fileSize"></p>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn" id="submitBtn">
                <i class="fas fa-upload"></i>
                Upload and Process
            </button>
        </form>

        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Processing your resume with NLP...</p>
        </div>

        <div class="results" id="results">
            <h3><i class="fas fa-chart-line"></i> Processing Results</h3>
            <div id="resultsContent"></div>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('pdfFile');
        const fileDisplay = document.getElementById('fileDisplay');
        const selectedFile = document.getElementById('selectedFile');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const uploadForm = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');
        const loading = document.getElementById('loading');
        const results = document.getElementById('results');
        const resultsContent = document.getElementById('resultsContent');

        // File input change handler
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.type !== 'application/pdf') {
                    alert('Please select a PDF file only.');
                    fileInput.value = '';
                    return;
                }

                if (file.size > 5 * 1024 * 1024) { // 5MB
                    alert('File size exceeds 5MB limit.');
                    fileInput.value = '';
                    return;
                }

                // Update display
                fileDisplay.classList.add('has-file');
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                selectedFile.classList.add('show');
            }
        });

        // Drag and drop handlers
        fileDisplay.addEventListener('dragover', function(e) {
            e.preventDefault();
            fileDisplay.style.borderColor = '#4285f4';
            fileDisplay.style.background = '#f0f7ff';
        });

        fileDisplay.addEventListener('dragleave', function(e) {
            e.preventDefault();
            fileDisplay.style.borderColor = '#dee2e6';
            fileDisplay.style.background = '#f8f9fa';
        });

        fileDisplay.addEventListener('drop', function(e) {
            e.preventDefault();
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });

        // Form submission
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const jobId = <?php
                $jobId = $_GET['jobId'] ?? '';
                echo json_encode($jobId); ?>;

            if (!jobId){
                alert("Upload link invalid.")
            }
            else {
                function decryptNumber(base64) {
                    const secret = 123;
                    const binaryStr = atob(base64);

                    let result = '';
                    for (let i = 0; i < binaryStr.length; i++) {
                        const xorByte = binaryStr.charCodeAt(i) ^ secret;
                        result += String.fromCharCode(xorByte);
                    }

                    return parseFloat(result); // Returns original number
                }

                jobId_real = decryptNumber(jobId)

                // alert("Upload link valid. Job ID: "+jobId_real); //For debug

                const formData = new FormData();
                formData.append('pdf_file', fileInput.files[0]);

                // Show loading
                loading.classList.add('show');
                results.classList.remove('show');
                submitBtn.disabled = true;

                // Submit form
                fetch('process_upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    loading.classList.remove('show');
                    submitBtn.disabled = false;

                    if (data.success) {
                        displayResults(data);
                    } else {
                        displayError(data.message || 'An error occurred during processing.');
                    }
                })
                .catch(error => {
                    loading.classList.remove('show');
                    submitBtn.disabled = false;
                    displayError('Network error: ' + error.message);
                });
            }
        });

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function displayResults(data) {
            let html = '';
            
            // File info
            html += `
                <div class="result-item">
                    <div class="result-label">Uploaded File</div>
                    <div class="result-value">${data.filename}</div>
                </div>
            `;

            // File path
            html += `
                <div class="result-item">
                    <div class="result-label">File Path</div>
                    <div class="result-value">${data.file_path}</div>
                </div>
            `;

            // NLP Results
            if (data.nlp_results) {
                html += `
                    <div class="result-item">
                        <div class="result-label">NLP Processing Results</div>
                        <div class="result-value">
                            <pre style="white-space: pre-wrap; font-family: monospace; background: #f8f9fa; padding: 12px; border-radius: 4px;">${data.nlp_results}</pre>
                        </div>
                    </div>
                `;
            }

            // Processing time
            if (data.processing_time) {
                html += `
                    <div class="result-item">
                        <div class="result-label">Processing Time</div>
                        <div class="result-value">${data.processing_time} seconds</div>
                    </div>
                `;
            }

            resultsContent.innerHTML = html;
            results.classList.add('show');
        }

        function displayError(message) {
            resultsContent.innerHTML = `
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>Error:</strong> ${message}
                </div>
            `;
            results.classList.add('show');
        }
    </script>
</body>
</html>
