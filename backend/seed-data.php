<?php
/**
 * Seed Database with Mock Data
 * Imports teams, players, and matches from the mock data
 */

require_once __DIR__ . '/bootstrap.php';

$pdo = get_pdo();

echo "=== CrickHub Database Seeding ===\n\n";

// Mock data (from scripts/data.js)
$teams = [
    ['id' => 'team-rcb', 'name' => 'Royal Challengers Bengaluru', 'city' => 'Bengaluru', 'coach' => 'Andy Flower', 'captain' => 'Faf du Plessis', 'founded' => 2008],
    ['id' => 'team-rr', 'name' => 'Rajasthan Royals', 'city' => 'Jaipur', 'coach' => 'Kumar Sangakkara', 'captain' => 'Sanju Samson', 'founded' => 2008],
    ['id' => 'team-gt', 'name' => 'Gujarat Titans', 'city' => 'Ahmedabad', 'coach' => 'Ashish Nehra', 'captain' => 'Shubman Gill', 'founded' => 2021],
    ['id' => 'team-mi', 'name' => 'Mumbai Indians', 'city' => 'Mumbai', 'coach' => 'Mark Boucher', 'captain' => 'Hardik Pandya', 'founded' => 2008],
    ['id' => 'team-csk', 'name' => 'Chennai Super Kings', 'city' => 'Chennai', 'coach' => 'Stephen Fleming', 'captain' => 'Ruturaj Gaikwad', 'founded' => 2008],
    ['id' => 'team-kkr', 'name' => 'Kolkata Knight Riders', 'city' => 'Kolkata', 'coach' => 'Chandrakant Pandit', 'captain' => 'Shreyas Iyer', 'founded' => 2008],
    ['id' => 'team-dc', 'name' => 'Delhi Capitals', 'city' => 'Delhi', 'coach' => 'Ricky Ponting', 'captain' => 'Rishabh Pant', 'founded' => 2008],
    ['id' => 'team-srh', 'name' => 'Sunrisers Hyderabad', 'city' => 'Hyderabad', 'coach' => 'Daniel Vettori', 'captain' => 'Pat Cummins', 'founded' => 2013],
    ['id' => 'team-pbks', 'name' => 'Punjab Kings', 'city' => 'Mohali', 'coach' => 'Trevor Bayliss', 'captain' => 'Shikhar Dhawan', 'founded' => 2008],
    ['id' => 'team-lsg', 'name' => 'Lucknow Super Giants', 'city' => 'Lucknow', 'coach' => 'Justin Langer', 'captain' => 'KL Rahul', 'founded' => 2022],
];

$players = [
    ['id' => 'virat-kohli', 'team_id' => 'team-rcb', 'name' => 'Virat Kohli', 'role' => 'Batter', 'matches' => 265, 'runs' => 9100, 'average' => 41.3, 'strike_rate' => 134.5, 'hundreds' => 7, 'fifties' => 45, 'fours' => 815, 'sixes' => 234, 'bio' => 'Right-handed top-order batter renowned for chasing and consistent run-scoring across formats. Former RCB captain and one of the greatest chasers in T20 cricket.'],
    ['id' => 'jos-buttler', 'team_id' => 'team-rr', 'name' => 'Jos Buttler', 'role' => 'Wicketkeeper Batter', 'matches' => 107, 'runs' => 3584, 'average' => 39.1, 'strike_rate' => 148.7, 'hundreds' => 5, 'fifties' => 22, 'fours' => 321, 'sixes' => 160, 'bio' => 'Explosive opener and wicketkeeper, central to England\'s white-ball revolution. Known for his aggressive batting and match-winning innings.'],
    ['id' => 'rashid-khan', 'team_id' => 'team-gt', 'name' => 'Rashid Khan', 'role' => 'Bowling All-rounder', 'matches' => 109, 'runs' => 722, 'average' => 14.4, 'strike_rate' => 154.3, 'hundreds' => 0, 'fifties' => 1, 'fours' => 58, 'sixes' => 58, 'bio' => 'World-class leg-spinner with deceptive googlies and powerful lower-order hitting. One of the most economical bowlers in T20 cricket.'],
    ['id' => 'suryakumar-yadav', 'team_id' => 'team-mi', 'name' => 'Suryakumar Yadav', 'role' => 'Batter', 'matches' => 139, 'runs' => 3243, 'average' => 32.7, 'strike_rate' => 146.1, 'hundreds' => 1, 'fifties' => 21, 'fours' => 356, 'sixes' => 109, 'bio' => 'Inventive 360-degree batter capable of dismantling bowling attacks across phases. Ranked #1 T20I batter in the world.'],
    ['id' => 'ben-stokes', 'team_id' => 'team-csk', 'name' => 'Ben Stokes', 'role' => 'All-rounder', 'matches' => 45, 'runs' => 920, 'average' => 25.5, 'strike_rate' => 135.8, 'hundreds' => 2, 'fifties' => 4, 'fours' => 70, 'sixes' => 45, 'bio' => 'Clutch all-rounder known for match-defining innings and relentless seam bowling. World Cup hero and match-winner.'],
    ['id' => 'rohit-sharma', 'team_id' => 'team-mi', 'name' => 'Rohit Sharma', 'role' => 'Batter', 'matches' => 243, 'runs' => 6211, 'average' => 30.3, 'strike_rate' => 130.6, 'hundreds' => 2, 'fifties' => 42, 'fours' => 554, 'sixes' => 257, 'bio' => 'Hitman of Indian cricket. Most successful IPL captain with 5 titles. Known for explosive batting and leadership.'],
    ['id' => 'ms-dhoni', 'team_id' => 'team-csk', 'name' => 'MS Dhoni', 'role' => 'Wicketkeeper Batter', 'matches' => 250, 'runs' => 5082, 'average' => 38.8, 'strike_rate' => 135.8, 'hundreds' => 0, 'fifties' => 24, 'fours' => 346, 'sixes' => 229, 'bio' => 'Captain Cool. Most successful IPL captain with 5 titles. Master finisher and tactical genius behind the stumps.'],
    ['id' => 'jasprit-bumrah', 'team_id' => 'team-mi', 'name' => 'Jasprit Bumrah', 'role' => 'Bowler', 'matches' => 120, 'runs' => 57, 'average' => 4.8, 'strike_rate' => 85.1, 'hundreds' => 0, 'fifties' => 0, 'fours' => 3, 'sixes' => 2, 'bio' => 'World-class fast bowler with unorthodox action. Known for yorkers and death bowling. India\'s premier pace bowler.'],
    ['id' => 'shubman-gill', 'team_id' => 'team-gt', 'name' => 'Shubman Gill', 'role' => 'Batter', 'matches' => 91, 'runs' => 2790, 'average' => 37.2, 'strike_rate' => 134.1, 'hundreds' => 3, 'fifties' => 18, 'fours' => 245, 'sixes' => 98, 'bio' => 'Elegant right-handed opener. Young batting sensation with classical technique and modern power-hitting abilities.'],
    ['id' => 'kl-rahul', 'team_id' => 'team-lsg', 'name' => 'KL Rahul', 'role' => 'Wicketkeeper Batter', 'matches' => 118, 'runs' => 4163, 'average' => 46.3, 'strike_rate' => 134.4, 'hundreds' => 4, 'fifties' => 33, 'fours' => 356, 'sixes' => 168, 'bio' => 'Versatile top-order batter and wicketkeeper. Known for elegant strokeplay and consistent run-scoring across formats.'],
    ['id' => 'rishabh-pant', 'team_id' => 'team-dc', 'name' => 'Rishabh Pant', 'role' => 'Wicketkeeper Batter', 'matches' => 98, 'runs' => 2838, 'average' => 34.6, 'strike_rate' => 147.9, 'hundreds' => 1, 'fifties' => 15, 'fours' => 218, 'sixes' => 130, 'bio' => 'Aggressive left-handed wicketkeeper-batter. Known for fearless batting and match-winning abilities. Dynamic player.'],
    ['id' => 'hardik-pandya', 'team_id' => 'team-mi', 'name' => 'Hardik Pandya', 'role' => 'All-rounder', 'matches' => 123, 'runs' => 2309, 'average' => 30.4, 'strike_rate' => 145.9, 'hundreds' => 0, 'fifties' => 10, 'fours' => 175, 'sixes' => 123, 'bio' => 'Powerful all-rounder with explosive batting and medium-pace bowling. Known for finishing games and taking crucial wickets.'],
    ['id' => 'ravindra-jadeja', 'team_id' => 'team-csk', 'name' => 'Ravindra Jadeja', 'role' => 'All-rounder', 'matches' => 226, 'runs' => 2692, 'average' => 26.4, 'strike_rate' => 127.6, 'hundreds' => 0, 'fifties' => 2, 'fours' => 218, 'sixes' => 90, 'bio' => 'Premier all-rounder with left-arm spin and explosive lower-order batting. Exceptional fielder and match-winner.'],
    ['id' => 'yuzvendra-chahal', 'team_id' => 'team-rr', 'name' => 'Yuzvendra Chahal', 'role' => 'Bowler', 'matches' => 145, 'runs' => 32, 'average' => 2.1, 'strike_rate' => 88.9, 'hundreds' => 0, 'fifties' => 0, 'fours' => 2, 'sixes' => 1, 'bio' => 'Crafty leg-spinner with excellent control. Highest wicket-taker for RCB. Known for deceiving batsmen with flight and turn.'],
    ['id' => 'david-warner', 'team_id' => 'team-dc', 'name' => 'David Warner', 'role' => 'Batter', 'matches' => 176, 'runs' => 6397, 'average' => 41.5, 'strike_rate' => 139.9, 'hundreds' => 4, 'fifties' => 61, 'fours' => 577, 'sixes' => 226, 'bio' => 'Explosive Australian opener. Orange Cap winner multiple times. One of the most consistent run-scorers in IPL history.'],
    ['id' => 'andre-russell', 'team_id' => 'team-kkr', 'name' => 'Andre Russell', 'role' => 'All-rounder', 'matches' => 112, 'runs' => 2262, 'average' => 29.0, 'strike_rate' => 174.0, 'hundreds' => 0, 'fifties' => 10, 'fours' => 144, 'sixes' => 193, 'bio' => 'Power-hitting all-rounder from West Indies. Known for massive sixes and crucial wickets. Most valuable player award winner.'],
    ['id' => 'sunil-narine', 'team_id' => 'team-kkr', 'name' => 'Sunil Narine', 'role' => 'All-rounder', 'matches' => 162, 'runs' => 1046, 'average' => 15.4, 'strike_rate' => 162.7, 'hundreds' => 0, 'fifties' => 1, 'fours' => 91, 'sixes' => 68, 'bio' => 'Mystery spinner and pinch-hitter. Two-time MVP winner. Known for economical bowling and explosive batting.'],
    ['id' => 'ruturaj-gaikwad', 'team_id' => 'team-csk', 'name' => 'Ruturaj Gaikwad', 'role' => 'Batter', 'matches' => 52, 'runs' => 1797, 'average' => 39.1, 'strike_rate' => 135.5, 'hundreds' => 1, 'fifties' => 14, 'fours' => 156, 'sixes' => 73, 'bio' => 'Elegant right-handed opener. Orange Cap winner. Known for timing and placement. Future star of Indian cricket.'],
    ['id' => 'pat-cummins', 'team_id' => 'team-srh', 'name' => 'Pat Cummins', 'role' => 'Bowler', 'matches' => 42, 'runs' => 379, 'average' => 18.0, 'strike_rate' => 141.4, 'hundreds' => 0, 'fifties' => 0, 'fours' => 28, 'sixes' => 18, 'bio' => 'World\'s #1 Test bowler. Australian pace spearhead. Known for accuracy, pace, and ability to bowl in all conditions.'],
    ['id' => 'glenn-maxwell', 'team_id' => 'team-rcb', 'name' => 'Glenn Maxwell', 'role' => 'All-rounder', 'matches' => 124, 'runs' => 2719, 'average' => 25.9, 'strike_rate' => 154.7, 'hundreds' => 0, 'fifties' => 18, 'fours' => 201, 'sixes' => 150, 'bio' => 'Big Show. Explosive middle-order batter and part-time off-spinner. Known for innovative shots and match-winning innings.'],
];

$matches = [
    ['id' => 'match-01', 'title' => 'RCB vs MI', 'home_team_id' => 'team-rcb', 'away_team_id' => 'team-mi', 'venue' => 'M. Chinnaswamy Stadium', 'match_date' => '2024-04-15', 'status' => 'Completed', 'result' => 'RCB won by 18 runs', 'summary' => 'Kohli top-scored with 82 off 47, while Siraj\'s 3/28 restricted Mumbai in a high-scoring thriller. RCB posted 196/5 and restricted MI to 178/8.'],
    ['id' => 'match-02', 'title' => 'GT vs RR', 'home_team_id' => 'team-gt', 'away_team_id' => 'team-rr', 'venue' => 'Narendra Modi Stadium', 'match_date' => '2024-04-16', 'status' => 'Completed', 'result' => 'RR won by 5 wickets', 'summary' => 'Buttler anchored the chase with an unbeaten 76 as Rajasthan hunted down 172 with an over spare. Sanju Samson contributed 42 runs.'],
    ['id' => 'match-03', 'title' => 'CSK vs KKR', 'home_team_id' => 'team-csk', 'away_team_id' => 'team-kkr', 'venue' => 'MA Chidambaram Stadium', 'match_date' => '2024-04-17', 'status' => 'Completed', 'result' => 'CSK won by 7 wickets', 'summary' => 'Ruturaj Gaikwad scored 67 and Ravindra Jadeja\'s 3/18 helped CSK defend 182. Deepak Chahar\'s opening burst derailed Kolkata\'s chase.'],
    ['id' => 'match-04', 'title' => 'SRH vs GT', 'home_team_id' => 'team-srh', 'away_team_id' => 'team-gt', 'venue' => 'Rajiv Gandhi International Stadium', 'match_date' => '2024-04-18', 'status' => 'Completed', 'result' => 'GT won by 3 runs', 'summary' => 'Rashid Khan defended 12 in the final over, sealing a nerve-wracking finish at Hyderabad. Shubman Gill scored 89 for GT.'],
    ['id' => 'match-05', 'title' => 'MI vs CSK', 'home_team_id' => 'team-mi', 'away_team_id' => 'team-csk', 'venue' => 'Wankhede Stadium', 'match_date' => '2024-04-19', 'status' => 'Completed', 'result' => 'MI won by 4 wickets', 'summary' => 'Suryakumar Yadav\'s explosive 83 off 35 balls helped Mumbai chase down 189 with 2 balls to spare. Rohit Sharma contributed 45.'],
    ['id' => 'match-06', 'title' => 'RR vs RCB', 'home_team_id' => 'team-rr', 'away_team_id' => 'team-rcb', 'venue' => 'Sawai Mansingh Stadium', 'match_date' => '2024-04-20', 'status' => 'Completed', 'result' => 'RR won by 6 wickets', 'summary' => 'Jos Buttler\'s century (103 off 61) powered Rajasthan to victory. Yuzvendra Chahal took 4/25 to restrict RCB to 183.'],
    ['id' => 'match-07', 'title' => 'KKR vs DC', 'home_team_id' => 'team-kkr', 'away_team_id' => 'team-dc', 'venue' => 'Eden Gardens', 'match_date' => '2024-04-21', 'status' => 'Completed', 'result' => 'DC won by 7 wickets', 'summary' => 'Rishabh Pant\'s unbeaten 65 and David Warner\'s 52 helped Delhi chase 195 with ease. Andre Russell scored 45 for KKR.'],
    ['id' => 'match-08', 'title' => 'LSG vs PBKS', 'home_team_id' => 'team-lsg', 'away_team_id' => 'team-pbks', 'venue' => 'BRSABV Ekana Cricket Stadium', 'match_date' => '2024-04-22', 'status' => 'Completed', 'result' => 'LSG won by 21 runs', 'summary' => 'KL Rahul\'s 74 and Marcus Stoinis\' quickfire 44 helped LSG post 199. PBKS fell short despite Shikhar Dhawan\'s 70.'],
    ['id' => 'match-09', 'title' => 'GT vs CSK', 'home_team_id' => 'team-gt', 'away_team_id' => 'team-csk', 'venue' => 'Narendra Modi Stadium', 'match_date' => '2024-04-23', 'status' => 'Completed', 'result' => 'CSK won by 63 runs', 'summary' => 'MS Dhoni\'s finishing cameo and Ravindra Jadeja\'s all-round performance (42 runs, 3 wickets) helped CSK dominate Gujarat.'],
    ['id' => 'match-10', 'title' => 'RCB vs SRH', 'home_team_id' => 'team-rcb', 'away_team_id' => 'team-srh', 'venue' => 'M. Chinnaswamy Stadium', 'match_date' => '2024-04-24', 'status' => 'Completed', 'result' => 'SRH won by 25 runs', 'summary' => 'Heinrich Klaasen\'s 67 and Pat Cummins\' 3/30 helped Hyderabad defend 207. Virat Kohli scored 51 for RCB in vain.'],
    ['id' => 'match-11', 'title' => 'MI vs RR', 'home_team_id' => 'team-mi', 'away_team_id' => 'team-rr', 'venue' => 'Wankhede Stadium', 'match_date' => '2024-04-25', 'status' => 'Scheduled', 'result' => null, 'summary' => 'High-octane clash between two powerhouses. Mumbai\'s batting vs Rajasthan\'s bowling attack promises an exciting contest.'],
    ['id' => 'match-12', 'title' => 'CSK vs RCB', 'home_team_id' => 'team-csk', 'away_team_id' => 'team-rcb', 'venue' => 'MA Chidambaram Stadium', 'match_date' => '2024-04-26', 'status' => 'Scheduled', 'result' => null, 'summary' => 'El Clasico of IPL. Dhoni vs Kohli. Chennai\'s spin-friendly pitch vs RCB\'s batting firepower. A classic rivalry renewed.'],
    ['id' => 'match-13', 'title' => 'KKR vs GT', 'home_team_id' => 'team-kkr', 'away_team_id' => 'team-gt', 'venue' => 'Eden Gardens', 'match_date' => '2024-04-27', 'status' => 'Scheduled', 'result' => null, 'summary' => 'Kolkata\'s home advantage vs Gujarat\'s consistent performance. Russell\'s power-hitting vs Rashid\'s spin magic.'],
    ['id' => 'match-14', 'title' => 'DC vs LSG', 'home_team_id' => 'team-dc', 'away_team_id' => 'team-lsg', 'venue' => 'Arun Jaitley Stadium', 'match_date' => '2024-04-28', 'status' => 'Scheduled', 'result' => null, 'summary' => 'Delhi\'s explosive batting lineup takes on Lucknow\'s balanced squad. Warner and Pant vs Rahul and Stoinis.'],
    ['id' => 'match-15', 'title' => 'PBKS vs SRH', 'home_team_id' => 'team-pbks', 'away_team_id' => 'team-srh', 'venue' => 'IS Bindra Stadium', 'match_date' => '2024-04-29', 'status' => 'Scheduled', 'result' => null, 'summary' => 'Punjab\'s batting depth vs Hyderabad\'s bowling attack. Shikhar Dhawan\'s leadership vs Pat Cummins\' pace.'],
];

// Function to generate UUID (simple version for seeding)
function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// Seed Teams
echo "1. Seeding Teams...\n";
$teamIdMap = [];
$stmt = $pdo->prepare('INSERT INTO teams (id, name, city, coach, captain, founded) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=name');
foreach ($teams as $team) {
    $teamIdMap[$team['id']] = $team['id'];
    $stmt->execute([
        $team['id'],
        $team['name'],
        $team['city'],
        $team['coach'],
        $team['captain'],
        $team['founded']
    ]);
    echo "  ✓ {$team['name']}\n";
}
echo "  → Inserted/Updated " . count($teams) . " teams\n\n";

// Seed Players
echo "2. Seeding Players...\n";
$playerIdMap = [];
$stmt = $pdo->prepare('INSERT INTO players (id, team_id, name, role, matches, runs, average, strike_rate, hundreds, fifties, fours, sixes, bio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=name');
foreach ($players as $player) {
    $playerIdMap[$player['id']] = $player['id'];
    $stmt->execute([
        $player['id'],
        $player['team_id'],
        $player['name'],
        $player['role'],
        $player['matches'],
        $player['runs'],
        $player['average'],
        $player['strike_rate'],
        $player['hundreds'],
        $player['fifties'],
        $player['fours'],
        $player['sixes'],
        $player['bio'] ?? null
    ]);
    echo "  ✓ {$player['name']} ({$player['role']})\n";
}
echo "  → Inserted/Updated " . count($players) . " players\n\n";

// Seed Matches
echo "3. Seeding Matches...\n";
$stmt = $pdo->prepare('INSERT INTO matches (id, title, home_team_id, away_team_id, venue, match_date, status, result, summary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title=title');
foreach ($matches as $match) {
    $stmt->execute([
        $match['id'],
        $match['title'],
        $match['home_team_id'],
        $match['away_team_id'],
        $match['venue'],
        $match['match_date'],
        $match['status'],
        $match['result'],
        $match['summary']
    ]);
    echo "  ✓ {$match['title']} ({$match['status']})\n";
}
echo "  → Inserted/Updated " . count($matches) . " matches\n\n";

// Verify counts
echo "4. Verifying data...\n";
$teamCount = $pdo->query('SELECT COUNT(*) FROM teams')->fetchColumn();
$playerCount = $pdo->query('SELECT COUNT(*) FROM players')->fetchColumn();
$matchCount = $pdo->query('SELECT COUNT(*) FROM matches')->fetchColumn();

echo "  ✓ Teams: {$teamCount}\n";
echo "  ✓ Players: {$playerCount}\n";
echo "  ✓ Matches: {$matchCount}\n\n";

echo "✅ Database seeding completed successfully!\n";
echo "\nYou can now:\n";
echo "  - View data at: http://localhost:8001/index.html\n";
echo "  - Manage data at: http://localhost:8001/admin.html\n";
echo "  - Login as admin: admin@crickhub.local / admin123\n";



