-- Actualiza la tabla de notificaciones para añadir una referencia a la entidad relacionada (ej. rutina)
ALTER TABLE `notificaciones`
ADD COLUMN `id_entidad_relacionada` INT NULL DEFAULT NULL AFTER `tipo`,
ADD INDEX `idx_id_entidad_relacionada` (`id_entidad_relacionada`);

-- Comentario sobre la nueva columna:
-- La columna 'id_entidad_relacionada' almacenará el ID de la entidad (ej. id_rutina) a la que se refiere la notificación.
-- Esto nos permitirá realizar acciones sobre esa entidad directamente desde la notificación (ej. marcar una rutina como completada).
