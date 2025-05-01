<?php
require_once './connection/connect.php';

try {
    
    $stmt1 = $pdo->prepare("SELECT m.match_id, m.match_date, m.home_score, m.away_score, 
                            t1.team_name AS team_home, t2.team_name AS team_away, 
                            t1.logo_url AS home_logo, t2.logo_url AS away_logo
                            FROM matches m
                            JOIN teams t1 ON m.home_team_id = t1.team_id
                            JOIN teams t2 ON m.away_team_id = t2.team_id
                            WHERE m.status = :status
                            ORDER BY m.match_date DESC");
    $stmt1->execute(['status' => 'completed']);
    $completed = $stmt1->fetchAll(PDO::FETCH_ASSOC);


    $stmt2 = $pdo->prepare("SELECT m.match_id, m.match_date, t1.team_name AS team_home, 
                            t2.team_name AS team_away, t1.logo_url AS home_logo, t2.logo_url AS away_logo
                            FROM matches m
                            JOIN teams t1 ON m.home_team_id = t1.team_id
                            JOIN teams t2 ON m.away_team_id = t2.team_id
                            WHERE m.status = :status
                            ORDER BY m.match_date ASC");
    $stmt2->execute(['status' => 'upcoming']);
    $upcoming = $stmt2->fetchAll(PDO::FETCH_ASSOC);


    $stmt3 = $pdo->prepare("SELECT p.player_name, t.team_name, SUM(g.goal_time) AS goals
                            FROM goals g
                            JOIN players p ON g.player_id = p.player_id
                            JOIN teams t ON p.team_id = t.team_id
                            GROUP BY p.player_id
                            ORDER BY goals DESC
                            LIMIT 10");
    $stmt3->execute();
    $scorers = $stmt3->fetchAll(PDO::FETCH_ASSOC);


    $stmt4 = $pdo->prepare("SELECT t.team_name, 
                            SUM(CASE WHEN m.home_score > m.away_score THEN 3 
                                     WHEN m.home_score = m.away_score THEN 1
                                     WHEN m.home_score < m.away_score THEN 0 END) AS points
                            FROM matches m
                            JOIN teams t ON m.home_team_id = t.team_id OR m.away_team_id = t.team_id
                            WHERE m.status = 'completed'
                            GROUP BY t.team_name
                            ORDER BY points DESC");
    $stmt4->execute();
    $standings = $stmt4->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error loading league data: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hope Haven Premier League</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        nav { background-color: #333; padding: 10px; }
        nav a { color: white; margin-right: 15px; text-decoration: none; }
        .subnav { background-color: white; padding: 10px 0; text-align: center; border-bottom: 1px solid #e0e0e0; }
        .subnav a { text-decoration: none; color: black; font-weight: bold; font-size: 16px; transition: color 0.3s; margin: 0 15px; }
        .container { padding: 20px; background-image: url('./images/kk1.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; height: 100vh; }
        .main-info { background-color: rgba(255, 255, 255, 0.8); padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); }
        .news-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        .wrapper { width: 100%; max-width: 1250px; margin: 0 auto; }
        .top-nav { margin-top: 100px; display: flex; align-items: center; padding: 0 20px; height: 65px; border-bottom: 1px solid #e0e0e0; }
        .logo-box { background-color:greenyellow; height: 100%; padding: 0 12px; display: flex; align-items: center; justify-content: center; }
        .logo-box img { height: 46px; width: auto; }
        .site-title { font-size: 18px; font-weight: bold; color:6cfa3a; margin-left: 15px; }
        .nav-links { display: flex; gap: 25px; margin-left: 50px; font-weight: bold; }
        .nav-links a { text-decoration: none; color: black; font-size: 16px; position: relative; padding-bottom: 5px; transition: color 0.2s ease-in-out; }
        .nav-links a:hover { color: #1a73e8; }
        .nav-links a:hover::after { content: ""; position: absolute; bottom: 0; left: 0; height: 2px; width: 100%; background-color: #1a73e8; }
        .nav-links a::after { content: ' ▾'; font-size: 12px; display: inline-block; }
        .nav-links a:last-child::after { content: ''; }
        .page-heading { text-align: center; margin-top: 20px; font-size: 2.5em; color: #333; }

        /* Standings Table */
        .standings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .standings-table th, .standings-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .standings-table th {
            background-color: #f4f4f4;
        }
        .league-info {
            background-image: url('./images/kk1.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            width: 100%;
        }
        .league-container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin: 10px auto;
            width: 80%;
        }

        /* Completed Matches Section */
        .completed-matches ul {
            list-style: none;
            padding: 0;
        }

        .completed-matches li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .completed-matches img {
            margin: 0 10px;
        }

        /* Upcoming Matches Section */
        .upcoming-matches ul {
            list-style: none;
            padding: 0;
        }

        .upcoming-matches li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #eaf4ff;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .upcoming-matches img {
            margin: 0 10px;
        }

        .upcoming-matches small {
            font-size: 0.9em;
            color: #555;
        }

        /* Top Scorers Section */
        .top-scorers ol {
            padding-left: 20px;
        }

        .top-scorers li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .top-scorers li span {
            font-weight: bold;
            color: #333;
        }

        /* League Standings Section */
        .standings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .standings-table th, .standings-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .standings-table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .standings-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .standings-table tr:hover {
            background-color: #e9e9e9;
        }
        .standings-table td:first-child {
            text-align: left;
            padding-left: 15px;
        }
        .league-container h2 {
            margin-top: 20px;
            font-size: 1.8em;
            color: #333;
            text-align: center;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        h1 {
            text-align: center;
            font-size: 3em;
            color: #333;
            margin-top: 20px;
            margin-bottom: 20px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="top-nav">
        <div class="logo-box">
            <img src="./images/claudia (1).gif" alt="Alliance Logo">
        </div>
        <div class="site-title">ALLIANCE</div>
        <div class="nav-links">
            <a href="#">All</a>
            <a href="#">Local news</a>
            <a href="sport.php">Sports</a>
            <a href="#">Entertainment</a>
            <li><a href="#" onclick="showContent(event, 'anounce')">School Announcement</a></li>
        </div>
    </div>
    </div>
    <h1>🏆 Hope Haven Premier League</h1>

<div class="league-info">
    <div class="league-container">
    
        <h2>✅ Completed Matches</h2>
        <div class="completed-matches">
            <ul>
                <?php foreach ($completed as $match): ?>
                    <li>
                        <img src="<?= htmlspecialchars($match['home_logo']) ?>" alt="Home Logo" width="40" height="40">
                        <span><?= htmlspecialchars($match['team_home']) ?></span>
                        <strong><?= (int)$match['home_score'] ?> - <?= (int)$match['away_score'] ?></strong>
                        <span><?= htmlspecialchars($match['team_away']) ?></span>
                        <img src="<?= htmlspecialchars($match['away_logo']) ?>" alt="Away Logo" width="40" height="40">
                        <small>(<?= htmlspecialchars($match['match_date']) ?>)</small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    
        <h2>📅 Upcoming Matches</h2>
        <div class="upcoming-matches">
            <ul>
                <?php foreach ($upcoming as $match): ?>
                    <li>
                        <img src="<?= htmlspecialchars($match['home_logo']) ?>" alt="Home Logo" width="40" height="40">
                        <span><?= htmlspecialchars($match['team_home']) ?></span>
                        <strong>vs</strong>
                        <span><?= htmlspecialchars($match['team_away']) ?></span>
                        <img src="<?= htmlspecialchars($match['away_logo']) ?>" alt="Away Logo" width="40" height="40">
                        <small>— <?= htmlspecialchars($match['match_date']) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Top Scorers Section -->
        <h2>🎯 Top Scorers</h2>
        <div class="top-scorers">
            <ol>
                <?php foreach ($scorers as $scorer): ?>
                    <li>
                        <span><?= htmlspecialchars($scorer['player_name']) ?></span>
                        <span>(<?= htmlspecialchars($scorer['team_name']) ?>)</span>
                        <span><?= (int)$scorer['goals'] ?> goals</span>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>

        <!-- Standings Table Section -->
        <h2>🏆 League Standings</h2>
        <table class="standings-table">
            <tr>
                <th>Team</th>
                <th>Points</th>
            </tr>
            <?php foreach ($standings as $team): ?>
                <tr>
                    <td><?= htmlspecialchars($team['team_name']) ?></td>
                    <td><?= (int)$team['points'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</div>
</body>
</html>
