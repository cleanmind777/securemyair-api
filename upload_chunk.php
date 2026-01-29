<?php
// Chunked upload handler for large files
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: access, Authorization');
header('Access-Control-Allow-Methods: POST, GET');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include "mydbCon.php";
include "auth.php";

$arr = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle chunk upload
    if (isset($_FILES["file"]) && isset($_POST["fileId"])) {
        $chunkIndex = intval($_POST["chunkIndex"]);
        $totalChunks = intval($_POST["totalChunks"]);
        $fileName = $_POST["fileName"];
        $fileId = $_POST["fileId"];
        $fileSize = intval($_POST["fileSize"]);
        $toLibrary = isset($_POST["to_library"]) ? intval($_POST["to_library"]) : 0;
        
        $uploadDir = "img/chunks/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $chunkFile = $uploadDir . $fileId . "_chunk_" . $chunkIndex;
        
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $chunkFile)) {
            $arr["success"] = true;
            $arr["chunk"] = $chunkIndex;
        } else {
            $arr["success"] = false;
            $arr["error"] = "Failed to save chunk";
        }
    }
    // Handle finalization
    elseif (isset($_GET["action"]) && $_GET["action"] === "finalize") {
        $fileId = $_GET["fileId"];
        $fileName = $_GET["fileName"];
        $fileSize = intval($_GET["fileSize"]);
        $toLibrary = isset($_GET["to_library"]) ? intval($_GET["to_library"]) : 0;
        
        $uploadDir = "img/chunks/";
        $chunkCount = 0;
        
        // Count available chunks
        for ($i = 0; $i < 1000; $i++) { // Reasonable limit
            $chunkFile = $uploadDir . $fileId . "_chunk_" . $i;
            if (file_exists($chunkFile)) {
                $chunkCount++;
            } else {
                break;
            }
        }
        
        if ($chunkCount > 0) {
            // Generate unique filename
            $file_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $unique_name = time() . "_" . uniqid() . "." . $file_ext;
            $finalFile = "img/" . $unique_name;
            
            $handle = fopen($finalFile, 'wb');
            if ($handle) {
                for ($i = 0; $i < $chunkCount; $i++) {
                    $chunkPath = $uploadDir . $fileId . "_chunk_" . $i;
                    if (file_exists($chunkPath)) {
                        $chunkData = file_get_contents($chunkPath);
                        fwrite($handle, $chunkData);
                        unlink($chunkPath); // Delete chunk after combining
                    }
                }
                fclose($handle);
                
                // Clean up chunks directory if empty
                @rmdir($uploadDir);
                
                // Insert into database
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $image_extensions = ["jpg", "jpeg", "png", "gif", "bmp", "webp"];
                $video_extensions = ["mp4", "webm", "ogg", "avi", "mov"];
                
                if (in_array($ext, $image_extensions) || in_array($ext, $video_extensions)) {
                    if ($toLibrary === 1) {
                        // Save into media_library
                        $file_name = mysqli_real_escape_string($dbCon, $fileName);
                        $file_type = in_array($ext, $video_extensions) ? "video" : "image";
                        $file_size = filesize($finalFile);
                        $duration = 30; // Default duration
                        
                        $insert_lib = "INSERT INTO `media_library` (`file_name`, `file_path`, `file_type`, `file_size`, `duration`) VALUES ('$file_name', '" . mysqli_real_escape_string($dbCon, $unique_name) . "', '$file_type', '$file_size', '$duration')";
                        ($ins_lib = mysqli_query($dbCon, $insert_lib)) or die("database error:" . mysqli_error($dbCon));
                        if ($ins_lib) {
                            $arr["success"] = true;
                            $arr["id"] = mysqli_insert_id($dbCon);
                            $arr["name"] = $fileName;
                            $arr["path"] = $unique_name;
                            $arr["type"] = $file_type;
                            $arr["size"] = $file_size;
                            $arr["time"] = $duration;
                        } else {
                            $arr["success"] = false;
                            $arr["error"] = "Database insert failed";
                        }
                    } else {
                        $arr["success"] = false;
                        $arr["error"] = "Only library uploads supported";
                    }
                } else {
                    $arr["success"] = false;
                    $arr["error"] = "Unsupported file type";
                }
            } else {
                $arr["success"] = false;
                $arr["error"] = "Failed to create final file";
            }
        } else {
            $arr["success"] = false;
            $arr["error"] = "No chunks found";
        }
    }
    else {
        $arr["success"] = false;
        $arr["error"] = "Invalid request";
    }
} else {
    $arr["success"] = false;
    $arr["error"] = "Invalid method";
}

print json_encode($arr);
?>