<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

function getDemoStatusFromElapsed($elapsedSeconds, $currentStatus) {
    if ($currentStatus === "cancelled") {
        return "cancelled";
    }

    $elapsedSeconds = max(0, (int) $elapsedSeconds);

    if ($elapsedSeconds < 10) {
        return "pending";
    }

    if ($elapsedSeconds < 20) {
        return "preparing";
    }

    if ($elapsedSeconds < 30) {
        return "ready";
    }

    return "delivered";
}

function labelStatus($status) {
    return ucfirst((string) $status);
}

try {
    $userId = (int) $_SESSION["user_id"];
    $role = $_SESSION["role"] ?? "customer";
    $isAdmin = in_array($role, ["admin", "system_admin"], true);
    $scope = $_GET["scope"] ?? "customer";
    $requestedOrderId = isset($_GET["order_id"]) ? (int) $_GET["order_id"] : 0;

    $where = "WHERE o.order_status != 'cancelled'";
    $params = [];

    if (!$isAdmin || $scope !== "admin") {
        $where .= " AND o.user_id = :user_id";
        $params[":user_id"] = $userId;
    }

    if ($requestedOrderId > 0) {
        $where .= " AND o.order_id = :order_id";
        $params[":order_id"] = $requestedOrderId;
    }

    $stmt = $conn->prepare("\n        SELECT\n            o.order_id,\n            o.order_number,\n            o.order_status,\n            o.payment_status,\n            TIMESTAMPDIFF(SECOND, o.created_at, NOW()) AS elapsed_seconds\n        FROM orders o\n        $where\n        ORDER BY o.created_at DESC\n    ");
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    $updateStmt = $conn->prepare("UPDATE orders SET order_status = :new_status WHERE order_id = :order_id");
    $historyStmt = $conn->prepare("\n        INSERT INTO order_status_history\n        (order_id, updated_by, old_status, new_status, remarks)\n        VALUES\n        (:order_id, NULL, :old_status, :new_status, 'Automatically updated by live demo timer')\n    ");

    foreach ($orders as &$order) {
        $oldStatus = $order["order_status"];
        $newStatus = getDemoStatusFromElapsed($order["elapsed_seconds"], $oldStatus);

        if ($newStatus !== $oldStatus) {
            $updateStmt->execute([
                ":new_status" => $newStatus,
                ":order_id" => (int) $order["order_id"]
            ]);

            $historyStmt->execute([
                ":order_id" => (int) $order["order_id"],
                ":old_status" => $oldStatus,
                ":new_status" => $newStatus
            ]);

            $order["order_status"] = $newStatus;
        }

        $order["label"] = labelStatus($order["order_status"]);
    }
    unset($order);

    $counts = [
        "all" => 0,
        "pending" => 0,
        "preparing" => 0,
        "ready" => 0,
        "delivered" => 0,
        "cancelled" => 0
    ];

    $countWhere = "WHERE 1=1";
    $countParams = [];

    if (!$isAdmin || $scope !== "admin") {
        $countWhere .= " AND user_id = :user_id";
        $countParams[":user_id"] = $userId;
    }

    $countStmt = $conn->prepare("SELECT order_status, COUNT(*) AS total FROM orders $countWhere GROUP BY order_status");
    $countStmt->execute($countParams);
    $countRows = $countStmt->fetchAll();

    foreach ($countRows as $row) {
        $status = $row["order_status"];
        $total = (int) $row["total"];
        $counts["all"] += $total;

        if (isset($counts[$status])) {
            $counts[$status] = $total;
        }
    }

    echo json_encode([
        "success" => true,
        "orders" => $orders,
        "counts" => $counts
    ]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
    exit;
}
?>
