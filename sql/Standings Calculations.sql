SELECT
	p.name AS `player`,
    p.defaultchar,
    COUNT(DISTINCT(rp.race)) AS races,
    SUM(CASE WHEN rp.position = 1 THEN '1' ELSE '0' END) AS stage_wins,
    ANY_VALUE(ch.wins) AS `champ_wins`
FROM championships AS c
INNER JOIN races AS r ON c.id = r.championship
INNER JOIN races_positions AS rp ON r.id = rp.race
INNER JOIN players AS p ON rp.player = p.id
LEFT JOIN
(
	SELECT
		COUNT(DISTINCT(id)) AS `wins`,
        champion
	FROM championships
    GROUP BY id
) AS ch ON ch.champion = p.id
WHERE valid = 1
AND `date` BETWEEN '2017-04-01 00:00:00' AND '2017-05-01 00:00:00'
GROUP BY p.name, p.defaultchar