ALTER TABLE teacher_class DROP FOREIGN KEY teacher_class_ibfk_1;

ALTER TABLE teacher_class
  ADD CONSTRAINT teacher_class_ibfk_1 FOREIGN KEY (teacher_id) REFERENCES teachers(id);
