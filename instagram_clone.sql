-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 06 May 2023, 13:14:36
-- Sunucu sürümü: 10.4.27-MariaDB
-- PHP Sürümü: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `instagram_clone`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `comments`
--

CREATE TABLE `comments` (
  `commentid` int(11) NOT NULL,
  `commenttext` varchar(250) NOT NULL,
  `commentpost` int(11) NOT NULL,
  `commentowner` int(11) NOT NULL,
  `createdat` varchar(24) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `comments`
--

INSERT INTO `comments` (`commentid`, `commenttext`, `commentpost`, `commentowner`, `createdat`) VALUES
(3, 'yaslandiq', 42, 3, 'Apr 28, 2023 - 06:23 PM'),
(4, 'yyyyy', 47, 4, 'Apr 30, 2023 - 01:17 AM'),
(6, 'xalq senisevir', 47, 4, 'May 01, 2023 - 12:42 AM'),
(10, 'uran', 47, 4, 'May 01, 2023 - 03:07 AM'),
(12, 'ttttttt', 47, 4, 'May 01, 2023 - 03:09 AM'),
(16, 'vetenim sensen', 47, 4, 'May 01, 2023 - 02:47 PM'),
(18, 'guuuu', 47, 4, 'May 03, 2023 - 12:34 AM'),
(19, 'guuuttttt', 47, 4, 'May 03, 2023 - 01:50 AM');

--
-- Tetikleyiciler `comments`
--
DELIMITER $$
CREATE TRIGGER `after_comment_insert` AFTER INSERT ON `comments` FOR EACH ROW BEGIN 
SET @sender_name=(SELECT username FROM users WHERE userid=new.commentowner);
SET @notificationstext=CONCAT(@sender_name,'has commented on your post');
SET @notificationsresource=new.commentpost;
SET @notificationstype=0;
SET @notificationsreceiver=(SELECT DISTINCT posts.postowner FROM posts INNER JOIN comments ON posts.postid=new.commentpost WHERE postid=new.commentpost);
SET @receivedat=(SELECT DATE_FORMAT(NOW(),'%b %e,%Y - %h:%i %p'));
IF   @notificationsreceiver!=new.commentowner THEN
INSERT INTO notifications( notificationtext,notificationresource,notificationtype,
notificationreceiver,receivedat) VALUES(@notificationstext,@notificationsresource, @notificationstype,@notificationsreceiver,@receivedat);
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `follows`
--

CREATE TABLE `follows` (
  `followerid` int(11) NOT NULL,
  `followingid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tetikleyiciler `follows`
--
DELIMITER $$
CREATE TRIGGER `after_follows_insert` AFTER INSERT ON `follows` FOR EACH ROW BEGIN 
SET @sender_name=(SELECT username FROM users WHERE userid=new.followerid);
SET @notificationstext=CONCAT(@sender_name,'started following you');
SET @notificationsresource=new.followerid;
SET @notificationstype=0;
SET @notificationsreceiver=(SELECT userid FROM users WHERE userid=new.followingid);
SET @receivedat=(SELECT DATE_FORMAT(NOW(),'%b %e,%Y - %h:%i %p'));
INSERT INTO notifications( notificationtext,notificationresource,notificationtype,
notificationreceiver,receivedat) VALUES(@notificationstext,@notificationsresource, @notificationstype,@notificationsreceiver,@receivedat);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `likedposts`
--

CREATE TABLE `likedposts` (
  `postid` int(11) NOT NULL,
  `userid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `likedposts`
--

INSERT INTO `likedposts` (`postid`, `userid`) VALUES
(41, 4),
(42, 4),
(47, 4);

--
-- Tetikleyiciler `likedposts`
--
DELIMITER $$
CREATE TRIGGER `after_post_likes` AFTER INSERT ON `likedposts` FOR EACH ROW BEGIN 
SET @sender_name=(SELECT username FROM users WHERE userid=new.userid);
SET @notificationstext=CONCAT(@sender_name,'liked your post');
SET @notificationsresource=new.postid;
SET @notificationstype=1;
SET @notificationsreceiver=(SELECT postowner FROM posts WHERE postid=new.postid);
SET @receivedat=(SELECT DATE_FORMAT(NOW(),'%b %e,%Y - %h:%i %p'));
IF   @notificationsreceiver!=new.userid THEN
INSERT INTO notifications( notificationtext,notificationresource,notificationtype,
notificationreceiver,receivedat) VALUES(@notificationstext,@notificationsresource, @notificationstype,@notificationsreceiver,@receivedat);
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `notifications`
--

CREATE TABLE `notifications` (
  `notificationid` int(11) NOT NULL,
  `notificationtext` text NOT NULL,
  `notificationresource` int(11) NOT NULL COMMENT 'userid or postid',
  `notificationtype` tinyint(1) NOT NULL COMMENT '0:user 1:post',
  `notificationreceiver` int(11) NOT NULL,
  `receivedat` varchar(24) NOT NULL,
  `isseen` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `notifications`
--

INSERT INTO `notifications` (`notificationid`, `notificationtext`, `notificationresource`, `notificationtype`, `notificationreceiver`, `receivedat`, `isseen`) VALUES
(1, 'vuqarhas commented on your post', 42, 0, 4, 'Apr 28,2023 - 06:08 PM', 1),
(2, 'vuqarhas commented on your post', 42, 0, 4, 'Apr 28,2023 - 06:12 PM', 1),
(3, 'vuqarhas commented on your post', 42, 0, 4, 'Apr 28,2023 - 06:23 PM', 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `posts`
--

CREATE TABLE `posts` (
  `postid` int(11) NOT NULL,
  `postphoto` varchar(100) NOT NULL,
  `postdescription` varchar(200) NOT NULL,
  `postowner` int(11) NOT NULL,
  `createdat` varchar(24) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `posts`
--

INSERT INTO `posts` (`postid`, `postphoto`, `postdescription`, `postowner`, `createdat`) VALUES
(47, 'uploading/1682706990.jpg', 'klubbbl', 4, 'Apr 28, 2023 - 10:36 PM');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `savedposts`
--

CREATE TABLE `savedposts` (
  `postid` int(11) NOT NULL,
  `userid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `savedposts`
--

INSERT INTO `savedposts` (`postid`, `userid`) VALUES
(0, 0),
(0, 0),
(0, 0),
(0, 0),
(0, 0),
(0, 0),
(0, 0),
(0, 0),
(0, 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `userid` int(11) NOT NULL,
  `useremail` varchar(50) NOT NULL,
  `username` varchar(25) NOT NULL,
  `userpassword` varchar(50) NOT NULL,
  `userfullname` varchar(50) DEFAULT NULL,
  `userphoto` varchar(100) NOT NULL DEFAULT 'default.png',
  `userbio` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `userprofileprivate` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`userid`, `useremail`, `username`, `userpassword`, `userfullname`, `userphoto`, `userbio`, `userprofileprivate`) VALUES
(2, 'Ayan@mail.ru', 'ayanhesenli', 'f69f8ea7585f1528f668405c5b7881d2', NULL, 'default.png', NULL, 0),
(3, 'vuqar@mail.ru', 'vuqar', 'e4702aafe810a9b355a871f5d963966f', NULL, 'default.png', NULL, 0),
(4, 'aqil@mail.ru', 'aqil', '3f088ebeda03513be71d34d214291986', 'Nail', 'default.png', 'men cox sevinirem', 0);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`commentid`);

--
-- Tablo için indeksler `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notificationid`);

--
-- Tablo için indeksler `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`postid`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userid`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `comments`
--
ALTER TABLE `comments`
  MODIFY `commentid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Tablo için AUTO_INCREMENT değeri `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notificationid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `posts`
--
ALTER TABLE `posts`
  MODIFY `postid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `userid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
