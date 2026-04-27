<?php

/**
 * Marca como finalizadas las estancias activas caducadas y elimina
 * solicitudes pendientes cuya fecha_fin ya pasó.
 *
 * Regla: una estancia con fecha_fin = 2100-01-01 se considera indefinida.
 *
 * @return array{archived_stays:int, deleted_requests:int}
 */
function expireStaysAndPendingRequests(mysqli $mysqli): array
{
    $archivedStays = 0;
    $deletedRequests = 0;

    $sqlArchive = "
        UPDATE stays
        SET status = 'archived',
            archived_at = COALESCE(archived_at, NOW())
        WHERE status = 'active'
          AND fecha_fin < CURDATE()
          AND fecha_fin <> '2100-01-01'
    ";
    if ($stmt = $mysqli->prepare($sqlArchive)) {
        $stmt->execute();
        $archivedStays = (int) $stmt->affected_rows;
        $stmt->close();
    }

    $sqlDelete = "
        DELETE FROM group_join_requests
        WHERE status = 'pending'
          AND fecha_fin < CURDATE()
    ";
    if ($stmt = $mysqli->prepare($sqlDelete)) {
        $stmt->execute();
        $deletedRequests = (int) $stmt->affected_rows;
        $stmt->close();
    }

    return [
        'archived_stays' => $archivedStays,
        'deleted_requests' => $deletedRequests,
    ];
}
