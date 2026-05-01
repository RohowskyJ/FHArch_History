<!DOCTYPE html>
<html>
<head>
    <title>Mitglieder-Liste</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Mitglieder-Liste</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Vorname</th>
                <th>E-Mail</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $member): ?>
            <tr>
                <td><?php echo htmlspecialchars($member['mi_id']); ?></td>
                <td><?php echo htmlspecialchars($member['fd_name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($member['fd_vname'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($member['fd_email'] ?? ''); ?></td>
                <td>
                    <a href="/members/edit/<?php echo $member['mi_id']; ?>">Bearbeiten</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>