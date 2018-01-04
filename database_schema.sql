CREATE DATABASE IF NOT EXISTS social_network;

USE social_network;

CREATE TABLE users (
  id int(10) auto_increment not null,
  role varchar (20),
  email varchar (255),
  name varchar (255),
  surname varchar (255),
  password varchar (255),
  nick varchar (100),
  bio varchar (255),
  active tinyint (1),
  image varchar (255),
  constraint users_uniques_fields unique (email, nick),
  constraint pk_users primary key (id)
) ENGINE = InnoDb;

CREATE TABLE publications (
  id int(10) auto_increment not null,
  user_id int (10),
  `text` varchar (255),
  document varchar (255),
  image varchar (255),
  status varchar (255),
  created_at datetime,
  constraint pk_publications primary key (id),
  constraint fk_publications_users FOREIGN KEY (user_id) references users(id)
) ENGINE = InnoDb;

CREATE TABLE following (
  id int(10) auto_increment not null,
  user int (10),
  `followed` int (10),
  constraint pk_following primary key (id),
  constraint fk_following_users FOREIGN KEY (user) references users(id),
  constraint fk_followed FOREIGN KEY (followed) references users(id)
) ENGINE = InnoDb;

CREATE TABLE private_messages (
  id int(10) auto_increment not null,
  message text,
  `emitter` int (10),
  receiver int (10),
  file varchar(255),
  image varchar(255),
  readed tinyint(1),
  created_at datetime,
  constraint pk_private_messages primary key (id),
  constraint fk_emitter_private FOREIGN KEY (emitter) references users(id),
  constraint fk_receiver_private FOREIGN KEY (receiver) references users(id)
) ENGINE = InnoDb;

CREATE TABLE likes (
  id int(10) auto_increment not null,
  `user` int (10),
  publication int (10),
  constraint pk_likes primary key (id),
  constraint fk_likes_user FOREIGN KEY (user) references users(id),
  constraint fk_likes_publication FOREIGN KEY (publication) references publications(id)
) ENGINE = InnoDb;

CREATE TABLE notifications (
  id int(10) auto_increment not null,
  `user_id` int (10),
  type varchar (255),
  type_id int (10),
  readed tinyint (1),
  created_at datetime,
  extra varchar (100),
  constraint pk_notifications primary key (id),
  constraint fk_notifications_user FOREIGN KEY (user_id) references users(id)
) ENGINE = InnoDb;
