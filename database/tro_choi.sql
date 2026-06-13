-- ============================================================
-- MINI GAMES – Thêm vào database english_learning
-- Chạy SAU du_lieu_day_du.sql
-- Chỉ bao gồm các bảng được sử dụng trong code
-- ============================================================
USE english_learning;
SET FOREIGN_KEY_CHECKS = 0;

-- Duel 1v1
CREATE TABLE IF NOT EXISTS duels (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  challenger       INT NOT NULL,
  opponent         INT DEFAULT NULL,
  status           ENUM('waiting','active','finished','expired') DEFAULT 'waiting',
  challenger_score INT DEFAULT 0,
  opponent_score   INT DEFAULT 0,
  winner_id        INT DEFAULT NULL,
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  finished_at      TIMESTAMP NULL,
  FOREIGN KEY (challenger) REFERENCES users(id) ON DELETE CASCADE
);

-- Điểm mini game (Word Puzzle, Memory Match, Typing Race)
CREATE TABLE IF NOT EXISTS game_scores (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  game_type  ENUM('wordle','match','typing','duel') NOT NULL,
  score      INT DEFAULT 0,
  details    JSON,
  played_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

SET FOREIGN_KEY_CHECKS = 1;
