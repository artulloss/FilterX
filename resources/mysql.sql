-- # !mysql
-- # { filter
-- #   { create
-- #     { players
CREATE TABLE IF NOT EXISTS filter_players (
  id INTEGER PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(16) NOT NULL
);
-- #     }
-- #     { soft_mutes
CREATE TABLE IF NOT EXISTS soft_mutes (
  id INTEGER UNIQUE REFERENCES filter_players(id),
  until INTEGER UNSIGNED
);
-- #     }
-- #   }
-- #   { insert
-- #     { players
-- #       :name string
INSERT INTO filter_players (name) VALUES (:name);
-- #     }
-- #   }
-- #   { upsert
-- #     { soft_mutes
-- #       :name string
-- #       :until int
INSERT INTO soft_mutes VALUES ((SELECT id FROM filter_players WHERE name = :name), :until) ON DUPLICATE KEY UPDATE until = :until;
-- #     }
-- #   }
-- #   { get
-- #     { player
-- #       :name string
SELECT * FROM filter_players WHERE name = :name;
-- #     }
-- #     { soft_mutes
-- #       :name string
SELECT until FROM soft_mutes WHERE id = (SELECT id FROM filter_players WHERE name = :name);
-- #     }
-- #   }
-- #   { delete
-- #     { soft_mute
-- #     :name string
DELETE FROM soft_mutes WHERE id = (SELECT id FROM filter_players WHERE name = :name);
-- #     }
-- #   }
-- # }