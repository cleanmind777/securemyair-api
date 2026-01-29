<?php
// Increase PHP upload limits to handle larger files
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

// Set headers to increase server limits
header('X-Accel-Buffering: no'); // Disable Nginx buffering
header('Connection: close'); // Close connection to free up resources

include('headers.php');
include('auth.php');
$arr = [];

// Unified CRUD API for advertisement_img table
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // GET: List ads for a machine/client
    if (isset($_GET["list"]) && $_GET["list"] == "1") {
        $api = isset($_GET["api"]) ? $_GET["api"] : null;
        $cid = isset($_GET["cid"]) ? intval($_GET["cid"]) : null;
        
        if ($api) {
            $whereClause = "a.machine_api = '" . mysqli_real_escape_string($dbCon, $api) . "'";
            if ($cid) {
                $whereClause .= " AND a.client_id = '$cid'";
            }
            
            $query = "SELECT a.id, a.media_id, a.ad_time, a.ads_order, a.client_id, a.machine_api, m.file_path, m.file_type 
                      FROM advertisement_img a 
                      LEFT JOIN media_library m ON m.id = a.media_id 
                      WHERE $whereClause 
                      ORDER BY a.ads_order ASC, a.id ASC";
            ($result = mysqli_query($dbCon, $query)) or die("database error:" . mysqli_error($dbCon));
            
            $items = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $path = $row["file_path"];
                $type = $row["file_type"] ? $row["file_type"] : (in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ["mp4","webm"]) ? "video" : "image");
                $items[] = [
                    "id" => intval($row["id"]),
                    "media_id" => intval($row["media_id"]),
                    "path" => $path,
                    "time" => intval($row["ad_time"]),
                    "order" => intval($row["ads_order"]),
                    "type" => $type,
                    "client_id" => $row["client_id"],
                    "machine_api" => $row["machine_api"]
                ];
            }
            $arr["items"] = $items;
        } else {
            $arr["res"] = "Missing api parameter";
        }
    }
    // GET: Get single ad by ID
    elseif (isset($_GET["id"])) {
        $id = intval($_GET["id"]);
        $query = "SELECT a.*, m.file_path, m.file_type 
                  FROM advertisement_img a 
                  LEFT JOIN media_library m ON m.id = a.media_id 
                  WHERE a.id = '$id'";
        ($result = mysqli_query($dbCon, $query)) or die("database error:" . mysqli_error($dbCon));
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $path = $row["file_path"];
            $type = $row["file_type"] ? $row["file_type"] : (in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ["mp4","webm"]) ? "video" : "image");
            $arr = [
                "id" => intval($row["id"]),
                "media_id" => intval($row["media_id"]),
                "path" => $path,
                "time" => intval($row["ad_time"]),
                "order" => intval($row["ads_order"]),
                "type" => $type,
                "client_id" => $row["client_id"],
                "machine_api" => $row["machine_api"]
            ];
        } else {
            $arr["res"] = "Ad not found";
        }
    }
    // GET: Get current ad (first in order)
    elseif (isset($_GET["api"])) {
        $api = $_GET["api"];
        $query = "SELECT a.ad_time, m.file_path FROM advertisement_img a LEFT JOIN media_library m ON m.id=a.media_id WHERE a.machine_api='" . mysqli_real_escape_string($dbCon, $api) . "' ORDER BY a.ads_order ASC, a.id ASC LIMIT 1";
        ($result = mysqli_query($dbCon, $query)) or die("database error:" . mysqli_error($dbCon));
        $adPic = mysqli_fetch_assoc($result);
        if ($adPic) { 
            $arr["path"] = $adPic["file_path"]; 
            $arr["time"] = $adPic["ad_time"]; 
        }
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST["action"]) ? $_POST["action"] : null;
    
    // CREATE: Add new ad to timeline
    if ($action === "create" || $action === "upload") {
        $api = isset($_POST["api"]) ? $_POST["api"] : null;
        $machine_id = isset($_POST["machine_id"]) ? intval($_POST["machine_id"]) : null;
        $media_id = isset($_POST["media_id"]) ? intval($_POST["media_id"]) : null;
        $order = isset($_POST["order"]) ? intval($_POST["order"]) : 0;
        $time = isset($_POST["time"]) ? intval($_POST["time"]) : null;
        $cid = isset($_POST["cid"]) ? intval($_POST["cid"]) : null;
        
        if (!$api) { 
            $arr["res"] = "Missing machine_api"; 
            print json_encode($arr); 
            return; 
        }
        
        // Resolve media_id if library_path is provided (backward compatibility)
        if (!$media_id && isset($_POST["library_path"])) {
            $libq = mysqli_query($dbCon, "SELECT id, duration FROM media_library WHERE file_path='" . mysqli_real_escape_string($dbCon, $_POST["library_path"]) . "' LIMIT 1");
            if ($libq && mysqli_num_rows($libq) > 0) {
                $lr = mysqli_fetch_assoc($libq);
                $media_id = intval($lr["id"]);
                if ($time === null) { $time = intval($lr["duration"]); }
            } else {
                $arr["res"] = "Media not found in library";
                print json_encode($arr);
                return;
            }
        }
        
        if (!$media_id) { 
            $arr["res"] = "Missing media_id"; 
            print json_encode($arr); 
            return; 
        }
        
        // Get duration from media_library if not explicitly provided
        if ($time === null) {
            $get_lib_duration = mysqli_query($dbCon, "SELECT `duration` FROM `media_library` WHERE `id`='$media_id' LIMIT 1");
            if ($get_lib_duration && mysqli_num_rows($get_lib_duration) > 0) {
                $lib_row = mysqli_fetch_assoc($get_lib_duration);
                $time = intval($lib_row["duration"]);
            } else {
                $time = 30; // Default if not found
            }
        }
        
        $machineIdVal = $machine_id ? "'" . $machine_id . "'" : "NULL";
        $machine_api = "'" . mysqli_real_escape_string($dbCon, $api) . "'";
        $clientIdVal = $cid ? "'" . $cid . "'" : "NULL";
        
        $insert = "INSERT INTO `advertisement_img` (`client_id`,`client_name`,`machine_id`,`machine_name`,`machine_api`,`media_id`,`ad_time`,`ads_order`) VALUES ($clientIdVal,NULL,$machineIdVal,NULL,$machine_api,'$media_id','$time','$order')";
        ($ins = mysqli_query($dbCon, $insert)) or die("database error:" . mysqli_error($dbCon));
        
        if ($ins) {
            $newId = mysqli_insert_id($dbCon);
            $arr["res"] = "true";
            $arr["id"] = $newId;
            $arr["media_id"] = $media_id;
            $arr["time"] = $time;
            $arr["order"] = $order;
        } else {
            $arr["res"] = "Failed to assign media to timeline";
        }
    }
    
    // UPDATE: Update ad properties
    elseif ($action === "update") {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
        if (!$id) {
            $arr["res"] = "Missing id";
            print json_encode($arr);
            return;
        }
        
        $updates = [];
        if (isset($_POST["media_id"])) {
            $media_id = intval($_POST["media_id"]);
            $updates[] = "`media_id`='$media_id'";
        }
        if (isset($_POST["ad_time"])) {
            $ad_time = intval($_POST["ad_time"]);
            $updates[] = "`ad_time`='$ad_time'";
        }
        if (isset($_POST["ads_order"])) {
            $ads_order = intval($_POST["ads_order"]);
            $updates[] = "`ads_order`='$ads_order'";
        }
        if (isset($_POST["machine_api"])) {
            $machine_api = "'" . mysqli_real_escape_string($dbCon, $_POST["machine_api"]) . "'";
            $updates[] = "`machine_api`=$machine_api";
        }
        if (isset($_POST["client_id"])) {
            $client_id = intval($_POST["client_id"]);
            $updates[] = "`client_id`='$client_id'";
        }
        
        if (empty($updates)) {
            $arr["res"] = "No fields to update";
        } else {
            $update_sql = "UPDATE `advertisement_img` SET " . implode(", ", $updates) . " WHERE `id`='$id'";
            ($upd = mysqli_query($dbCon, $update_sql)) or die("database error:" . mysqli_error($dbCon));
            $arr["res"] = $upd ? "true" : "Failed to update";
        }
    }
    
    // BULK UPDATE: Update multiple ads
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
            
            if (isset($update["media_id"])) {
                $updateFields[] = "`media_id`='" . intval($update["media_id"]) . "'";
            }
            if (isset($update["ad_time"])) {
                $updateFields[] = "`ad_time`='" . intval($update["ad_time"]) . "'";
            }
            if (isset($update["ads_order"])) {
                $updateFields[] = "`ads_order`='" . intval($update["ads_order"]) . "'";
            }
            
            if (!empty($updateFields)) {
                $sql = "UPDATE `advertisement_img` SET " . implode(", ", $updateFields) . " WHERE `id`='$id'";
                ($ok = mysqli_query($dbCon, $sql)) or die("database error:" . mysqli_error($dbCon));
                if (!$ok) $success = false;
            }
        }
        $arr["res"] = $success ? "true" : "Some updates failed";
    }
    
    // DELETE: Delete single ad
    elseif ($action === "delete" || $action === "delete_ad") {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
        if ($id) {
            $delete = "DELETE FROM `advertisement_img` WHERE `id`='$id'";
            ($del = mysqli_query($dbCon, $delete)) or die("database error:" . mysqli_error($dbCon));
            $arr["res"] = $del ? "true" : "false";
        } else {
            $arr["res"] = "Missing id";
        }
    }
    
    // BULK DELETE: Delete multiple ads
    elseif ($action === "bulk_delete") {
        $ids = isset($_POST["ids"]) ? json_decode($_POST["ids"], true) : null;
        if (!$ids || !is_array($ids)) {
            $arr["res"] = "Invalid ids data";
            print json_encode($arr);
            return;
        }
        
        $idList = implode(",", array_map('intval', $ids));
        $delete = "DELETE FROM `advertisement_img` WHERE `id` IN ($idList)";
        ($del = mysqli_query($dbCon, $delete)) or die("database error:" . mysqli_error($dbCon));
        $arr["res"] = $del ? "true" : "false";
        $arr["deleted_count"] = mysqli_affected_rows($dbCon);
    }
    
    // LEGACY ACTIONS (for backward compatibility)
    elseif ($action === "replace_media") {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
        $media_id = isset($_POST["media_id"]) ? intval($_POST["media_id"]) : null;
        if ($id && $media_id) {
            $upd = mysqli_query($dbCon, "UPDATE `advertisement_img` SET `media_id`='$media_id' WHERE `id`='$id'");
            $arr["res"] = $upd ? "true" : "Failed";
        } else {
            $arr["res"] = "Missing id or media_id";
        }
    }
    
    elseif ($action === "update_positions") {
        $positionsRaw = isset($_POST["positions"]) ? $_POST["positions"] : null;
        if ($positionsRaw) {
            $positions = json_decode($positionsRaw, true);
            if (is_array($positions)) {
                foreach ($positions as $pos) {
                    $id = intval($pos["id"]);
                    $orderVal = isset($pos["position"]) ? intval($pos["position"]) : (isset($pos["order"]) ? intval($pos["order"]) : null);
                    if ($orderVal === null) { continue; }
                    $sql = "UPDATE `advertisement_img` SET `ads_order`='".$orderVal."' WHERE `id`='".$id."'";
                    ($ok = mysqli_query($dbCon, $sql)) or die("database error:" . mysqli_error($dbCon));
                }
                $arr["res"] = "true";
            } else { 
                $arr["res"] = "Invalid positions payload"; 
            }
        } else { 
            $arr["res"] = "Missing positions"; 
        }
    }
    
    elseif ($action === "update_time") {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : null;
        $time = isset($_POST["time"]) ? intval($_POST["time"]) : null;
        if ($id && $time) {
            $sql = "UPDATE `advertisement_img` SET `ad_time`='$time' WHERE `id`='$id'";
            ($result = mysqli_query($dbCon, $sql)) or die("database error:" . mysqli_error($dbCon));
            $arr["res"] = $result ? "true" : "Failed to update time";
        } else {
            $arr["res"] = "Missing id or time";
        }
    }
    
    // Update all ads referencing a specific media_id with new duration
    elseif ($action === "update_media_duration") {
        $media_id = isset($_POST["media_id"]) ? intval($_POST["media_id"]) : null;
        $time = isset($_POST["time"]) ? intval($_POST["time"]) : null;
        
        if ($media_id && $time) {
            $update = "UPDATE `advertisement_img` SET `ad_time`='$time' WHERE `media_id`='$media_id'";
            ($upd = mysqli_query($dbCon, $update)) or die("database error:" . mysqli_error($dbCon));
            $arr["res"] = $upd ? "true" : "false";
            $arr["updated_count"] = mysqli_affected_rows($dbCon);
        } else {
            $arr["res"] = "Missing media_id or time";
        }
    }
    
    // Assign media to all machines (replaces upload_to_all_machines and append_library_to_all)
    elseif ($action === "assign_media_to_all" || $action === "upload_to_all_machines" || $action === "append_library_to_all") {
        $media_id = isset($_POST["media_id"]) ? intval($_POST["media_id"]) : null;
        $time = isset($_POST["time"]) ? intval($_POST["time"]) : 30;
        
        if (!$media_id) {
            $arr["res"] = "Missing media_id";
            print json_encode($arr);
            return;
        }
        
        // Check if media exists
        $media_check = "SELECT id FROM media_library WHERE id = '$media_id'";
        $media_result = mysqli_query($dbCon, $media_check);
        if (!$media_result) {
            $arr["res"] = "Database error checking media: " . mysqli_error($dbCon);
            print json_encode($arr);
            return;
        }
        if (mysqli_num_rows($media_result) == 0) {
            $arr["res"] = "Media not found with ID: $media_id";
            print json_encode($arr);
            return;
        }
        
        // Get ALL machines from all clients
        $machines_query = "SELECT Id, apiToken, customerId FROM machines";
        $machines_result = mysqli_query($dbCon, $machines_query);
        
        if (!$machines_result) {
            $arr["res"] = "Database error querying machines: " . mysqli_error($dbCon);
            print json_encode($arr);
            return;
        }
        
        $added_count = 0;
        $errors = [];
        $machine_count = mysqli_num_rows($machines_result);
        
        if ($machine_count > 0) {
            while ($machine = mysqli_fetch_assoc($machines_result)) {
                $machine_id = intval($machine["id"]);
                $machine_api = mysqli_real_escape_string($dbCon, $machine["apiToken"]);
                $client_id = intval($machine["customerId"]);
                
                // Get current max order for this machine
                $order_query = "SELECT MAX(ads_order) as max_order FROM advertisement_img WHERE machine_api = '$machine_api'";
                $order_result = mysqli_query($dbCon, $order_query);
                $max_order = 0;
                if ($order_result && mysqli_num_rows($order_result) > 0) {
                    $order_row = mysqli_fetch_assoc($order_result);
                    $max_order = intval($order_row["max_order"]) + 1;
                }
                
                $insert = "INSERT INTO `advertisement_img` (`client_id`,`machine_id`,`machine_api`,`media_id`,`ad_time`,`ads_order`) VALUES ('$client_id','$machine_id','$machine_api','$media_id','$time','$max_order')";
                if (mysqli_query($dbCon, $insert)) {
                    $added_count++;
                } else {
                    $errors[] = "Failed to insert for machine $machine_api: " . mysqli_error($dbCon);
                }
            }
        } else {
            // Fallback: get machines from existing ads (all clients)
            $fallback_query = "SELECT DISTINCT machine_api, client_id FROM advertisement_img";
            $fallback_result = mysqli_query($dbCon, $fallback_query);
            if ($fallback_result && mysqli_num_rows($fallback_result) > 0) {
                while ($machine = mysqli_fetch_assoc($fallback_result)) {
                    $machine_api = mysqli_real_escape_string($dbCon, $machine["machine_api"]);
                    $client_id = intval($machine["client_id"]);
                    
                    // Get current max order for this machine
                    $order_query = "SELECT MAX(ads_order) as max_order FROM advertisement_img WHERE machine_api = '$machine_api'";
                    $order_result = mysqli_query($dbCon, $order_query);
                    $max_order = 0;
                    if ($order_result && mysqli_num_rows($order_result) > 0) {
                        $order_row = mysqli_fetch_assoc($order_result);
                        $max_order = intval($order_row["max_order"]) + 1;
                    }
                    
                    $insert = "INSERT INTO `advertisement_img` (`client_id`,`machine_api`,`media_id`,`ad_time`,`ads_order`) VALUES ('$client_id','$machine_api','$media_id','$time','$max_order')";
                    if (mysqli_query($dbCon, $insert)) {
                        $added_count++;
                    } else {
                        $errors[] = "Failed to insert for machine $machine_api: " . mysqli_error($dbCon);
                    }
                }
            }
        }
        
        if ($added_count > 0) {
            $arr["res"] = "true";
            $arr["added_count"] = $added_count;
            $arr["debug"] = [
                "machine_count" => $machine_count,
                "media_id" => $media_id,
                "time" => $time
            ];
            if (!empty($errors)) {
                $arr["warnings"] = $errors;
            }
        } else {
            $arr["res"] = "No machines found or all insertions failed";
            $arr["debug"] = [
                "machine_count" => $machine_count,
                "media_id" => $media_id,
                "time" => $time
            ];
            if (!empty($errors)) {
                $arr["errors"] = $errors;
            }
        }
    }
    
    // Delete all ads for a client
    elseif ($action === "delete_all_ads") {
        $cid = isset($_POST["cid"]) ? intval($_POST["cid"]) : null;
        if ($cid) {
            $delete = "DELETE FROM `advertisement_img` WHERE `client_id`='$cid'";
            ($del = mysqli_query($dbCon, $delete)) or die("database error:" . mysqli_error($dbCon));
            $arr["res"] = $del ? "true" : "false";
            $arr["deleted_count"] = mysqli_affected_rows($dbCon);
        } else {
            $arr["res"] = "Missing cid";
        }
    }
    
    // Delete all ads for a specific machine
    elseif ($action === "delete_machine_ads") {
        $api = isset($_POST["api"]) ? $_POST["api"] : null;
        if ($api) {
            $delete = "DELETE FROM `advertisement_img` WHERE `machine_api`='" . mysqli_real_escape_string($dbCon, $api) . "'";
            ($del = mysqli_query($dbCon, $delete)) or die("database error:" . mysqli_error($dbCon));
            $arr["res"] = $del ? "true" : "false";
            $arr["deleted_count"] = mysqli_affected_rows($dbCon);
        } else {
            $arr["res"] = "Missing api";
        }
    }
    
    // Update all ads time for a specific machine
    elseif ($action === "update_machine_times") {
        $api = isset($_POST["api"]) ? $_POST["api"] : null;
        $time = isset($_POST["time"]) ? intval($_POST["time"]) : null;
        if ($api && $time) {
            $update = "UPDATE `advertisement_img` SET `ad_time`='$time' WHERE `machine_api`='" . mysqli_real_escape_string($dbCon, $api) . "'";
            ($upd = mysqli_query($dbCon, $update)) or die("database error:" . mysqli_error($dbCon));
            $arr["res"] = $upd ? "true" : "false";
            $arr["updated_count"] = mysqli_affected_rows($dbCon);
        } else {
            $arr["res"] = "Missing api or time";
        }
    }
    
    // Update all ads time for a specific client across all machines
    elseif ($action === "update_all_times") {
        $cid = isset($_POST["cid"]) ? intval($_POST["cid"]) : null;
        $time = isset($_POST["time"]) ? intval($_POST["time"]) : null;
        if ($cid && $time) {
            $update = "UPDATE `advertisement_img` SET `ad_time`='$time' WHERE `client_id`='$cid'";
            ($upd = mysqli_query($dbCon, $update)) or die("database error:" . mysqli_error($dbCon));
            $arr["res"] = $upd ? "true" : "false";
            $arr["updated_count"] = mysqli_affected_rows($dbCon);
        } else {
            $arr["res"] = "Missing cid or time";
        }
    }
}

print json_encode($arr);
?>