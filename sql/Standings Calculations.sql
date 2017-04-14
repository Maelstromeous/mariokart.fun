SELECT
	p.name AS `player`,
    p.defaultchar,
    COUNT(DISTINCT(rp.race)) AS races,
    SUM(CASE WHEN rp.position = 1 THEN '1' ELSE '0' END) AS stage_wins,
    COUNT(DISTINCT(c.id)) AS champ_wins
FROM players AS p
INNER JOIN races_positions AS rp ON p.id = rp.player
LEFT JOIN championships AS c ON p.id = c.champion
GROUP BY p.name, p.defaultchar