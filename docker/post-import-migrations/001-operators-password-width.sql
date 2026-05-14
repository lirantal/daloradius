-- Widen legacy daloRADIUS operator passwords before the Docker init script
-- writes a PHP password_hash() value for DALORADIUS_ADMIN_PASSWORD.
ALTER TABLE `operators` MODIFY `password` VARCHAR(95) NOT NULL;
