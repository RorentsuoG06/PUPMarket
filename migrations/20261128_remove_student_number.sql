USE db_pupmarket;

SET @student_number_column_exists = (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'users'
      AND column_name = 'student_number'
);

SET @student_number_migration = IF(
    @student_number_column_exists > 0,
    'ALTER TABLE users DROP COLUMN student_number',
    'SELECT 1'
);

PREPARE remove_student_number FROM @student_number_migration;
EXECUTE remove_student_number;
DEALLOCATE PREPARE remove_student_number;
