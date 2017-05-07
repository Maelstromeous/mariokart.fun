SELECT 
    dt.*,
    SUM(dt.champ_wins / dt.championships) AS `champ_win_perc`,
    SUM(dt.stage_wins / dt.stages) AS `stage_win_perc`,
    ANY_VALUE
    (
        # If player hasn't played more than the total championships / total players - 10% then they're disqualified
        CASE WHEN dt.championships > ((total_champs.count / total_players.count) * 0.9) THEN '1' ELSE '0' END
    ) AS `qualifies`,
    ANY_VALUE((total_champs.count / total_players.count) * 0.9) AS `qualification_limit`
FROM
(
    SELECT
        p.name AS `player`,
        p.defaultchar,
        COUNT(DISTINCT(c.id)) AS championships,
        COUNT(DISTINCT(sp.stage)) AS stages,
        SUM(CASE WHEN sp.position = 1 THEN '1' ELSE '0' END) AS stage_wins,
        ANY_VALUE(ch.wins) AS `champ_wins`
    FROM championships AS c
    INNER JOIN stages AS s ON c.id = s.championship
    INNER JOIN stages_positions AS sp ON s.id = sp.stage
    INNER JOIN players AS p ON sp.player = p.id
    LEFT JOIN
    (
        SELECT
            COUNT(DISTINCT(id)) AS `wins`,
            champion
        FROM championships
        GROUP BY id
    ) AS ch ON ch.champion = p.id
    WHERE valid = 1
    AND `date` BETWEEN '2017-04-01' AND '2017-05-01'
    GROUP BY p.name, p.defaultchar
) AS dt,
(
    # Get total number of championships to run qualitification checks
    SELECT 
        COUNT(id) AS `count`
    FROM championships
) AS `total_champs`,
(
    # Get the total number of participated players for qualification checks
    SELECT 
        COUNT(DISTINCT(p.id)) AS `count`
    FROM players AS p
    INNER JOIN stages_positions AS sp ON sp.player = p.id
) AS `total_players`
GROUP BY dt.player, dt.defaultchar
ORDER BY champ_win_perc DESC, stage_win_perc DESC, champ_wins DESC

