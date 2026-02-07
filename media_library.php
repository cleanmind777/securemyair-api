<?php
// Media Library API for managing uploaded media
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');
ini_set('file_uploads', '1');
ini_set('max_file_uploads', '20');

include 'headers.php';
header('Access-Control-Allow-Credentials: true');

$arr = [];

// Unified CRUD API for media_library table
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // GET: List all media
    if (isset($_GET["list"]) && $_GET["list"] == "1") {
        $query = "SELECT * FROM media_library ORDER BY created_at DESC";
        ($result = mysqli_query($dbCon, $query)) or die("database error:" . mysqli_error($dbCon));
        
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = [
                "id" => intval($row["id"]),
                "name" => $row["file_name"],
                "path" => $row["file_path"],
                "type" => $row["file_type"],
                "size" => intval($row["file_size"]),
                "time" => intval($row["duration"]),
                "created_at" => $row["created_at"],
                "updated_at" => $row["updated_at"]
            ];
        }
        $arr["items"] = $items;
    }
    // GET: Get single media by ID
    elseif (isset($_GET["id"])) {
        $id = intval($_GET["id"]);
        $query = "SELECT * FROM media_library WHERE id = '$id'";
        ($result = mysqli_query($dbCon, $query)) or die("database error:" . mysqli_error($dbCon));
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $arr = [
                "id" => intval($row["id"]),
                "name" => $row["file_name"],
                "path" => $row["file_path"],
                "type" => $row["file_type"],
                "size" => intval($row["file_size"]),
                "time" => intval($row["duration"]),
                "created_at" => $row["created_at"],
                "updated_at" => $row["updated_at"]
            ];
        } else {
            $arr["res"] = "Media not found";
        }
    }
    // GET: Search media by name or type
    elseif (isset($_GET["search"])) {
        $search = mysqli_real_escape_string($dbCon, $_GET["search"]);
        $type_filter = isset($_GET["type"]) ? " AND file_type = '" . mysqli_real_escape_string($dbCon, $_GET["type"]) . "'" : "";
        
        $query = "SELECT * FROM media_library WHERE file_name LIKE '%$search%'$type_filter ORDER BY created_at DESC";
        ($result = mysqli_query($dbCon, $query)) or die("database error:" . mysqli_error($dbCon));
        
        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = [
                "id" => intval($row["id"]),
                "name" => $row["file_name"],
                "path" => $row["file_path"],
                "type" => $row["file_type"],
                "size" => intval($row["file_size"]),
                "time" => intval($row["duration"]),
                "created_at" => $row["created_at"],
                "updated_at" => $row["updated_at"]
            ];
        }
        $arr["items"] = $items;
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST["action"]) ? $_POST["action"] : null;
    
    // CREATE: Upload new media
    if ($action === "create" || $action === "upload") {
        if (isset($_FILES["fileToUpload"])) {
            $target_dir = "img/";
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                if (!mkdir($target_dir, 0755, true)) {
                    $arr["res"] = "Failed to create upload directory";
                    print json_encode($arr);
                    return;
                }
            }
            
            // Check if directory is writable
            if (!is_writable($target_dir)) {
                $arr["res"] = "Upload directory is not writable";
                print json_encode($arr);
                return;
            }
            
            $file = $_FILES["fileToUpload"];
            $file_name = basename($file["name"]);
            $file_size = $file["size"];
            $file_tmp = $file["tmp_name"];
            $file_type = $file["type"];
            
            // Check for upload errors
            if ($file["error"] !== UPLOAD_ERR_OK) {
                $arr["res"] = "Upload error: " . $file["error"];
                print json_encode($arr);
                return;
            }
            
            // Determine if it's image or video
            $image_extensions = ["jpg", "jpeg", "png", "gif", "bmp", "webp"];
            $video_extensions = ["mp4", "webm", "ogg", "avi", "mov"];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $image_extensions)) {
                $media_type = "image";
            } elseif (in_array($file_ext, $video_extensions)) {
                $media_type = "video";
            } else {
                $arr["res"] = "Unsupported file type: " . $file_ext;
                print json_encode($arr);
                return;
            }
            
            // Generate unique filename
            $unique_name = time() . "_" . uniqid() . "." . $file_ext;
            $target_file = $target_dir . $unique_name;
            
            // Debug information
            $arr["debug"] = [
                "target_dir" => $target_dir,
                "target_file" => $target_file,
                "file_tmp" => $file_tmp,
                "file_exists" => file_exists($file_tmp),
                "is_uploaded_file" => is_uploaded_file($file_tmp),
                "dir_exists" => file_exists($target_dir),
                "dir_writable" => is_writable($target_dir),
                "file_error" => $file["error"],
                "file_size" => $file_size,
                "upload_max_filesize" => ini_get('upload_max_filesize'),
                "post_max_size" => ini_get('post_max_size'),
                "max_file_uploads" => ini_get('max_file_uploads'),
                "file_uploads" => ini_get('file_uploads'),
                "current_dir" => getcwd(),
                "absolute_target" => realpath($target_dir) ?: $target_dir
            ];
            
            if (move_uploaded_file($file_tmp, $target_file)) {
                $duration = isset($_POST["duration"]) ? intval($_POST["duration"]) : 30;
                
                $insert = "INSERT INTO media_library (file_name, file_path, file_type, file_size, duration) VALUES ('$file_name', '$unique_name', '$media_type', '$file_size', '$duration')";
                ($ins = mysqli_query($dbCon, $insert)) or die("database error:" . mysqli_error($dbCon));
                
                if ($ins) {
                    $newId = mysqli_insert_id($dbCon);
                    $arr["res"] = "true";
                    $arr["id"] = $newId;
                    $arr["name"] = $file_name;
                    $arr["path"] = $unique_name;
                    $arr["type"] = $media_type;
                    $arr["size"] = $file_size;
                    $arr["time"] = $duration;
                    unset($arr["debug"]); // Remove debug info on success
                } else {
                    $arr["res"] = "Failed to save media info";
                }
            } else {
                $arr["res"] = "Failed to move uploaded file. Check directory permissions.";
            }
        } else {
            $arr["res"] = "No file uploaded";
        }
    }
    
    // UPDATE: Update media properties
    elseif ($action === "update") {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
        if (!$id) {
            $arr["res"] = "Missing id";
            print json_encode($arr);
            return;
        }
        
        $updates = [];
        if (isset($_POST["file_name"])) {
            $file_name = mysqli_real_escape_string($dbCon, $_POST["file_name"]);
            $updates[] = "`file_name`='$file_name'";
        }
        if (isset($_POST["duration"])) {
            $duration = intval($_POST["duration"]);
            $updates[] = "`duration`='$duration'";
        }
        if (isset($_POST["file_type"])) {
            $file_type = mysqli_real_escape_string($dbCon, $_POST["file_type"]);
            $updates[] = "`file_type`='$file_type'";
        }
        
        if (empty($updates)) {
            $arr["res"] = "No fields to update";
        } else {
            $update_sql = "UPDATE `media_library` SET " . implode(", ", $updates) . " WHERE `id`='$id'";
            ($upd = mysqli_query($dbCon, $update_sql)) or die("database error:" . mysqli_error($dbCon));
            $arr["res"] = $upd ? "true" : "Failed to update";
        }
    }
    
    // BULK UPDATE: Update multiple media items
    elseif ($action === "bulk_update") {
        $updates = isset($_POST["updates"]) ? json_decode($_POST["updates"], true) : null;
        if (!$updates || !is_array($updates)) {
            $arr["res"] = "Invalid updates data";
            print json_encode($arr);
            return;
        }
        
        $success = true;
        foreach ($updates as $update) {
            $id = intval($update["id"]);
            $updateFields = [];
            
            if (isset($update["file_name"])) {
                $updateFields[] = "`file_name`='" . mysqli_real_escape_string($dbCon, $update["file_name"]) . "'";
            }
            if (isset($update["duration"])) {
                $updateFields[] = "`duration`='" . intval($update["duration"]) . "'";
            }
            if (isset($update["file_type"])) {
                $updateFields[] = "`file_type`='" . mysqli_real_escape_string($dbCon, $update["file_type"]) . "'";
            }
            
            if (!empty($updateFields)) {
                $sql = "UPDATE `media_library` SET " . implode(", ", $updateFields) . " WHERE `id`='$id'";
                ($ok = mysqli_query($dbCon, $sql)) or die("database error:" . mysqli_error($dbCon));
                if (!$ok) $success = false;
            }
        }
        $arr["res"] = $success ? "true" : "Some updates failed";
    }
    
    // DELETE: Delete single media
    elseif ($action === "delete") {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
        if ($id) {
            // Get file path before deleting
            $get_path = "SELECT `file_path` FROM `media_library` WHERE `id`='$id'";
            ($path_result = mysqli_query($dbCon, $get_path)) or die("database error:" . mysqli_error($dbCon));
            
            if ($path_result && mysqli_num_rows($path_result) > 0) {
                $path_row = mysqli_fetch_assoc($path_result);
                $file_path = $path_row["file_path"];
                
                // Delete from media_library
                $delete = "DELETE FROM `media_library` WHERE `id`='$id'";
                ($del = mysqli_query($dbCon, $delete)) or die("database error:" . mysqli_error($dbCon));
                
                // Delete from advertisement_img (timeline assignments) where media_id matches
                $delete_assignments = "DELETE FROM `advertisement_img` WHERE `media_id`='$id'";
                mysqli_query($dbCon, $delete_assignments);
                
                // Delete physical file
                if (file_exists('img/' . $file_path)) {
                    unlink('img/' . $file_path);
                }
                
                $arr["res"] = "true";
            } else {
                $arr["res"] = "Media not found";
            }
        } else {
            $arr["res"] = "Missing id";
        }
    }
    
    // BULK DELETE: Delete multiple media items
    elseif ($action === "bulk_delete") {
        $ids = isset($_POST["ids"]) ? json_decode($_POST["ids"], true) : null;
        if (!$ids || !is_array($ids)) {
            $arr["res"] = "Invalid ids data";
            print json_encode($arr);
            return;
        }
        
        $deleted_count = 0;
        foreach ($ids as $id) {
            $id = intval($id);
            
            // Get file path before deleting
            $get_path = "SELECT `file_path` FROM `media_library` WHERE `id`='$id'";
            ($path_result = mysqli_query($dbCon, $get_path)) or die("database error:" . mysqli_error($dbCon));
            
            if ($path_result && mysqli_num_rows($path_result) > 0) {
                $path_row = mysqli_fetch_assoc($path_result);
                $file_path = $path_row["file_path"];
                
                // Delete from media_library
                $delete = "DELETE FROM `media_library` WHERE `id`='$id'";
                ($del = mysqli_query($dbCon, $delete)) or die("database error:" . mysqli_error($dbCon));
                
                if ($del) {
                    // Delete from advertisement_img (timeline assignments) where media_id matches
                    $delete_assignments = "DELETE FROM `advertisement_img` WHERE `media_id`='$id'";
                    mysqli_query($dbCon, $delete_assignments);
                    
                    // Delete physical file
                    if (file_exists('img/' . $file_path)) {
                        unlink('img/' . $file_path);
                    }
                    
                    $deleted_count++;
                }
            }
        }
        
        $arr["res"] = "true";
        $arr["deleted_count"] = $deleted_count;
    }
    
    // LEGACY ACTIONS (for backward compatibility)
    elseif ($action === "update_duration") {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
        $duration = isset($_POST["duration"]) ? intval($_POST["duration"]) : null;
        if ($id && $duration) {
            $update = "UPDATE `media_library` SET `duration`='$duration' WHERE `id`='$id'";
            ($upd = mysqli_query($dbCon, $update)) or die("database error:" . mysqli_error($dbCon));
            $arr["res"] = $upd ? "true" : "Failed to update duration";
        } else {
            $arr["res"] = "Missing id or duration";
        }
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "PUT") {
    // PUT: Update media properties (RESTful)
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input["id"]) ? intval($input["id"]) : null;
    
    if (!$id) {
        $arr["res"] = "Missing id";
        print json_encode($arr);
        return;
    }
    
    $updates = [];
    if (isset($input["file_name"])) {
        $file_name = mysqli_real_escape_string($dbCon, $input["file_name"]);
        $updates[] = "`file_name`='$file_name'";
    }
    if (isset($input["duration"])) {
        $duration = intval($input["duration"]);
        $updates[] = "`duration`='$duration'";
    }
    if (isset($input["file_type"])) {
        $file_type = mysqli_real_escape_string($dbCon, $input["file_type"]);
        $updates[] = "`file_type`='$file_type'";
    }
    
    if (empty($updates)) {
        $arr["res"] = "No fields to update";
    } else {
        $update_sql = "UPDATE `media_library` SET " . implode(", ", $updates) . " WHERE `id`='$id'";
        ($upd = mysqli_query($dbCon, $update_sql)) or die("database error:" . mysqli_error($dbCon));
        $arr["res"] = $upd ? "true" : "Failed to update";
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    // DELETE: Delete media (RESTful)
    $id = isset($_GET["id"]) ? intval($_GET["id"]) : null;
    if ($id) {
        // Get file path before deleting
        $get_path = "SELECT `file_path` FROM `media_library` WHERE `id`='$id'";
        ($path_result = mysqli_query($dbCon, $get_path)) or die("database error:" . mysqli_error($dbCon));
        
        if ($path_result && mysqli_num_rows($path_result) > 0) {
            $path_row = mysqli_fetch_assoc($path_result);
            $file_path = $path_row["file_path"];
            
            // Delete from media_library
            $delete = "DELETE FROM `media_library` WHERE `id`='$id'";
            ($del = mysqli_query($dbCon, $delete)) or die("database error:" . mysqli_error($dbCon));
            
            // Delete from advertisement_img (timeline assignments) where media_id matches
            $delete_assignments = "DELETE FROM `advertisement_img` WHERE `media_id`='$id'";
            mysqli_query($dbCon, $delete_assignments);
            
            // Delete physical file
            if (file_exists('img/' . $file_path)) {
                unlink('img/' . $file_path);
            }
            
            $arr["res"] = "true";
        } else {
            $arr["res"] = "Media not found";
        }
    } else {
        $arr["res"] = "Missing id";
    }
}

print json_encode($arr);
?>