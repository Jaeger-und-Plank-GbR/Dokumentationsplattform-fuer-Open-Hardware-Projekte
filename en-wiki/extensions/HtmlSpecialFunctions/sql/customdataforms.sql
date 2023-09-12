CREATE TABLE IF NOT EXISTS /*_*/customdtaforms (
      formID integer NOT NULL PRIMARY KEY AUTO_INCREMENT,
      data text NOT NULL,
      userID integer unsigned NOT NULL DEFAULT 0
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
