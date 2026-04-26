<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PHP Upload Configuration Test</h2>";

// 1. معلومات PHP
echo "<h3>1. PHP Info:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Loaded php.ini: " . php_ini_loaded_file() . "<br><br>";

// 2. إعدادات الرفع
echo "<h3>2. Upload Settings:</h3>";
echo "file_uploads: " . ini_get('file_uploads') . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "upload_tmp_dir: " . ini_get('upload_tmp_dir') . "<br>";
echo "sys_temp_dir: " . sys_get_temp_dir() . "<br><br>";

// 3. اختبار المجلد المؤقت
echo "<h3>3. Temp Directory Test:</h3>";
$tempDir = sys_get_temp_dir();
echo "Temp Dir: $tempDir<br>";
echo "Exists: " . (is_dir($tempDir) ? 'Yes' : 'No') . "<br>";
echo "Writable: " . (is_writable($tempDir) ? 'Yes' : 'No') . "<br>";

// محاولة إنشاء ملف اختبار
$testFile = $tempDir . '/test_' . time() . '.txt';
$canWrite = @file_put_contents($testFile, 'test');
echo "Can Create File: " . ($canWrite !== false ? 'Yes' : 'No') . "<br>";
if ($canWrite) {
    @unlink($testFile);
}
echo "<br>";

// 4. متغيرات البيئة
echo "<h3>4. Environment Variables:</h3>";
echo "TMPDIR: " . getenv('TMPDIR') . "<br>";
echo "TEMP: " . getenv('TEMP') . "<br>";
echo "TMP: " . getenv('TMP') . "<br><br>";

// 5. اختبار رفع ملف فعلي
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    echo "<h3>5. Upload Test Result:</h3>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
    
    if ($_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
        echo "<strong style='color:green'>✅ Upload Successful!</strong><br>";
        echo "File saved to: " . $_FILES['test_file']['tmp_name'] . "<br>";
    } else {
        echo "<strong style='color:red'>❌ Upload Failed!</strong><br>";
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'PHP extension stopped upload'
        ];
        echo "Error: " . ($errors[$_FILES['test_file']['error']] ?? 'Unknown error') . "<br>";
    }
}
?>

<h3>Test Upload Form:</h3>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="test_file">
    <button type="submit">Upload Test File</button>
</form>