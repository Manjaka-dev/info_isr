CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    contenu MEDIUMTEXT NOT NULL, -- Adapté pour le HTML de TinyMCE
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (date_creation) -- Notre fameux index de performance
) ENGINE=InnoDB;

ALTER TABLE articles ADD COLUMN resume TEXT AFTER titre;

ALTER TABLE articles ADD COLUMN slug VARCHAR(255) AFTER titre;
CREATE INDEX idx_slug ON articles(slug);

ALTER TABLE articles ADD COLUMN image_url VARCHAR(255) AFTER slug;