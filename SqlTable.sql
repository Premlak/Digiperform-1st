SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
CREATE TABLE admin (
id int(11) NOT NULL,
email text NOT NULL,
password text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `admin` (`id`, `email`, `password`) VALUES
(1, 'Godara', '1124');
CREATE TABLE affiliations (
id int(11) NOT NULL,
name varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE categories (
id int(11) NOT NULL,
name varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE ccategory (
id int(11) NOT NULL,
name text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE childccategory (
id int(11) NOT NULL,
name text NOT NULL,
subcCategory_id int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE cities (
id int(11) NOT NULL,
name varchar(100) NOT NULL,
state_id int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE coursedata (
id int(11) NOT NULL,
realcourse_id int(11) DEFAULT NULL,
content text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE courses (
id int(11) NOT NULL,
name varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE course_durations (
id int(11) NOT NULL,
duration varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE entrance_exams (
id int(11) NOT NULL,
category_id int(11) DEFAULT NULL,
name varchar(100) NOT NULL,
content text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE entrance_exam_categories (
id int(11) NOT NULL,
name varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE news (
id int(11) NOT NULL,
category_id int(11) DEFAULT NULL,
title varchar(255) NOT NULL,
content text NOT NULL,
created_at timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE news_categories (
id int(11) NOT NULL,
name varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE program_types (
id int(11) NOT NULL,
name enum('Full Time','Part Time','On Campus','Online','Distance','Off Campus') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE questions (
id int(11) NOT NULL,
exam_id int(11) DEFAULT NULL,
content longtext DEFAULT NULL,
name text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE realcourses (
id int(11) NOT NULL,
name varchar(255) DEFAULT NULL,
subcategory_id int(11) DEFAULT NULL,
duration text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE realjob (
id int(11) NOT NULL,
name text NOT NULL,
salary text DEFAULT NULL,
qualification text DEFAULT NULL,
exam_required text DEFAULT NULL,
content text DEFAULT NULL,
childcCategory_id int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE states (
id int(11) NOT NULL,
name varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE streams (
id int(11) NOT NULL,
name varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE subadmin (
id int(11) NOT NULL,
email text NOT NULL,
password text NOT NULL,
handler text NOT NULL,
updated_at datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE subcategories (
id int(11) NOT NULL,
name varchar(255) DEFAULT NULL,
course_id int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE subccategory (
id int(11) NOT NULL,
name text NOT NULL,
ccategory_id int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE sub_streams (
id int(11) NOT NULL,
name varchar(255) NOT NULL,
ucourse_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE ucourses (
id int(11) NOT NULL,
stream_id int(11) DEFAULT NULL,
name varchar(255) NOT NULL,
type enum('Degree','Diploma','Certificate') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE universities (
id int(11) NOT NULL,
name varchar(255) NOT NULL,
logo varchar(255) DEFAULT NULL,
state_id int(11) DEFAULT NULL,
city_id int(11) DEFAULT NULL,
affiliation_id int(11) DEFAULT NULL,
type enum('Private','Government') DEFAULT NULL,
category text DEFAULT NULL,
rank int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE university_courses (
id int(11) NOT NULL,
university_id int(11) DEFAULT NULL,
course_id int(11) DEFAULT NULL,
avg_fee_per_year int(11) DEFAULT NULL,
duration_id int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE university_course_entrance_exams (
university_course_id int(11) NOT NULL,
entrance_exam_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE university_course_program_types (
university_course_id int(11) NOT NULL,
program_type_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE university_course_substreams (
university_course_id int(11) NOT NULL,
substream_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE university_streams (
university_id int(11) NOT NULL,
stream_id int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
CREATE TABLE footer_user (
id int(11) NOT NULL,
email TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE admin ADD PRIMARY KEY (id);
ALTER TABLE affiliations ADD PRIMARY KEY (id);
ALTER TABLE categories ADD PRIMARY KEY (id);
ALTER TABLE ccategory ADD PRIMARY KEY (id);
ALTER TABLE childccategory ADD PRIMARY KEY (id), ADD KEY subcCategory_id (subcCategory_id);
ALTER TABLE cities ADD PRIMARY KEY (id), ADD KEY state_id (state_id);
ALTER TABLE coursedata ADD PRIMARY KEY (id), ADD UNIQUE KEY realcourse_id (realcourse_id);
ALTER TABLE courses ADD PRIMARY KEY (id);
ALTER TABLE course_durations ADD PRIMARY KEY (id);
ALTER TABLE entrance_exams ADD PRIMARY KEY (id), ADD KEY category_id (category_id);
ALTER TABLE entrance_exam_categories ADD PRIMARY KEY (id);
ALTER TABLE news ADD PRIMARY KEY (id), ADD KEY category_id (category_id);
ALTER TABLE news_categories ADD PRIMARY KEY (id);
ALTER TABLE program_types ADD PRIMARY KEY (id), ADD UNIQUE KEY name (name);
ALTER TABLE questions ADD PRIMARY KEY (id);
ALTER TABLE realcourses ADD PRIMARY KEY (id), ADD KEY subcategory_id (subcategory_id);
ALTER TABLE realjob ADD PRIMARY KEY (id), ADD KEY entrance_exam_id (exam_required(768)), ADD KEY childcCategory_id (childcCategory_id);
ALTER TABLE states ADD PRIMARY KEY (id), ADD UNIQUE KEY name (name);
ALTER TABLE streams ADD PRIMARY KEY (id);
ALTER TABLE subadmin ADD PRIMARY KEY (id);
ALTER TABLE subcategories ADD PRIMARY KEY (id), ADD KEY course_id (course_id);
ALTER TABLE subccategory ADD PRIMARY KEY (id), ADD KEY ccategory_id (ccategory_id);
ALTER TABLE sub_streams ADD PRIMARY KEY (id), ADD KEY ucourse_id (ucourse_id);
ALTER TABLE ucourses ADD PRIMARY KEY (id), ADD KEY stream_id (stream_id);
ALTER TABLE universities ADD PRIMARY KEY (id), ADD KEY state_id (state_id), ADD KEY city_id (city_id), ADD KEY affiliation_id (affiliation_id);
ALTER TABLE university_courses ADD PRIMARY KEY (id), ADD KEY university_id (university_id), ADD KEY course_id (course_id), ADD KEY duration_id (duration_id);
ALTER TABLE university_course_entrance_exams ADD PRIMARY KEY (university_course_id,entrance_exam_id), ADD KEY entrance_exam_id (entrance_exam_id);
ALTER TABLE university_course_program_types ADD PRIMARY KEY (university_course_id,program_type_id), ADD KEY program_type_id (program_type_id);
ALTER TABLE university_course_substreams ADD PRIMARY KEY (university_course_id,substream_id), ADD KEY substream_id (substream_id);
ALTER TABLE university_streams ADD PRIMARY KEY (university_id,stream_id), ADD KEY stream_id (stream_id);
ALTER TABLE admin MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE affiliations MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE categories MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE ccategory MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE childccategory MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE cities MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE coursedata MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE courses MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE course_durations MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE entrance_exams MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE entrance_exam_categories MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE news MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE news_categories MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE program_types MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE questions MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE realcourses MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE realjob MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE states MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE streams MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE subadmin MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE subcategories MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE subccategory MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE sub_streams MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE ucourses MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE universities MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE university_courses MODIFY id int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE childccategory ADD CONSTRAINT childccategory_ibfk_1 FOREIGN KEY (subcCategory_id) REFERENCES subccategory (id) ON DELETE CASCADE;
ALTER TABLE cities ADD CONSTRAINT cities_ibfk_1 FOREIGN KEY (state_id) REFERENCES states (id) ON DELETE CASCADE;
ALTER TABLE coursedata ADD CONSTRAINT coursedata_ibfk_1 FOREIGN KEY (realcourse_id) REFERENCES realcourses (id) ON DELETE CASCADE;
ALTER TABLE entrance_exams ADD CONSTRAINT entrance_exams_ibfk_1 FOREIGN KEY (category_id) REFERENCES entrance_exam_categories (id) ON DELETE CASCADE;
ALTER TABLE news ADD CONSTRAINT news_ibfk_1 FOREIGN KEY (category_id) REFERENCES news_categories (id) ON DELETE CASCADE;
ALTER TABLE realcourses ADD CONSTRAINT realcourses_ibfk_1 FOREIGN KEY (subcategory_id) REFERENCES subcategories (id) ON DELETE CASCADE;
ALTER TABLE realjob ADD CONSTRAINT realjob_ibfk_2 FOREIGN KEY (childcCategory_id) REFERENCES childccategory (id) ON DELETE CASCADE;
ALTER TABLE subcategories ADD CONSTRAINT subcategories_ibfk_1 FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE;
ALTER TABLE subccategory ADD CONSTRAINT subccategory_ibfk_1 FOREIGN KEY (ccategory_id) REFERENCES ccategory (id) ON DELETE CASCADE;
ALTER TABLE sub_streams ADD CONSTRAINT sub_streams_ibfk_1 FOREIGN KEY (ucourse_id) REFERENCES ucourses (id) ON DELETE CASCADE;
ALTER TABLE ucourses ADD CONSTRAINT ucourses_ibfk_1 FOREIGN KEY (stream_id) REFERENCES streams (id) ON DELETE CASCADE;
ALTER TABLE universities ADD CONSTRAINT universities_ibfk_1 FOREIGN KEY (state_id) REFERENCES states (id) ON DELETE SET NULL, ADD CONSTRAINT universities_ibfk_2 FOREIGN KEY (city_id) REFERENCES cities (id) ON DELETE SET NULL, ADD CONSTRAINT universities_ibfk_3 FOREIGN KEY (affiliation_id) REFERENCES affiliations (id) ON DELETE SET NULL;
ALTER TABLE university_courses ADD CONSTRAINT university_courses_ibfk_1 FOREIGN KEY (university_id) REFERENCES universities (id) ON DELETE CASCADE, ADD CONSTRAINT university_courses_ibfk_2 FOREIGN KEY (course_id) REFERENCES ucourses (id) ON DELETE CASCADE, ADD CONSTRAINT university_courses_ibfk_3 FOREIGN KEY (duration_id) REFERENCES course_durations (id) ON DELETE SET NULL;
ALTER TABLE university_course_entrance_exams ADD CONSTRAINT university_course_entrance_exams_ibfk_1 FOREIGN KEY (university_course_id) REFERENCES university_courses (id) ON DELETE CASCADE, ADD CONSTRAINT university_course_entrance_exams_ibfk_2 FOREIGN KEY (entrance_exam_id) REFERENCES entrance_exams (id) ON DELETE CASCADE;
ALTER TABLE university_course_program_types ADD CONSTRAINT university_course_program_types_ibfk_1 FOREIGN KEY (university_course_id) REFERENCES university_courses (id) ON DELETE CASCADE, ADD CONSTRAINT university_course_program_types_ibfk_2 FOREIGN KEY (program_type_id) REFERENCES program_types (id) ON DELETE CASCADE;
ALTER TABLE university_course_substreams ADD CONSTRAINT university_course_substreams_ibfk_1 FOREIGN KEY (university_course_id) REFERENCES university_courses (id) ON DELETE CASCADE, ADD CONSTRAINT university_course_substreams_ibfk_2 FOREIGN KEY (substream_id) REFERENCES sub_streams (id) ON DELETE CASCADE;
ALTER TABLE university_streams ADD CONSTRAINT university_streams_ibfk_1 FOREIGN KEY (university_id) REFERENCES universities (id) ON DELETE CASCADE, ADD CONSTRAINT university_streams_ibfk_2 FOREIGN KEY (stream_id) REFERENCES streams (id) ON DELETE CASCADE;
ALTER TABLE footer_user ADD PRIMARY KEY(id);
COMMIT;