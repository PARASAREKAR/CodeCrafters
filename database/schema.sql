-- EventHub Database Dump
-- Generated on 2026-07-21 21:22:48

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table structure for table `attendance`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `Attendance_ID` int NOT NULL AUTO_INCREMENT,
  `Registration_ID` int NOT NULL,
  `Status` enum('Present','Absent') NOT NULL DEFAULT 'Absent',
  `marked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Attendance_ID`),
  KEY `Registration_ID` (`Registration_ID`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`Registration_ID`) REFERENCES `registrations` (`Registration_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `attendance`
-- (No data to dump for table `attendance`)

-- --------------------------------------------------------
-- Table structure for table `contact_messages`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `contact_messages`
INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `submitted_at`) VALUES
('1', 'Parth S. Tupe', 'allogins.work@gmail.com', 'Review of EventHub', 'Your website is very nice.', '2026-07-21 09:58:32');

-- --------------------------------------------------------
-- Table structure for table `events`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `Event_ID` int NOT NULL AUTO_INCREMENT,
  `Event_Name` varchar(100) NOT NULL,
  `Description` text,
  `Venue` varchar(100) DEFAULT NULL,
  `Event_Date` date NOT NULL,
  `Event_Time` time DEFAULT NULL,
  `Organizer` varchar(100) DEFAULT NULL,
  `Capacity` int NOT NULL DEFAULT '100',
  `Event_Category` varchar(50) NOT NULL DEFAULT 'General',
  `Image_Path` varchar(255) DEFAULT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Approved',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Event_ID`),
  KEY `idx_event_date` (`Event_Date`),
  KEY `idx_event_name` (`Event_Name`),
  KEY `created_by` (`created_by`),
  KEY `idx_event_category` (`Event_Category`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`User_ID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `events`
INSERT INTO `events` (`Event_ID`, `Event_Name`, `Description`, `Venue`, `Event_Date`, `Event_Time`, `Organizer`, `Capacity`, `Event_Category`, `Image_Path`, `Status`, `created_by`, `created_at`) VALUES
('1', 'Bengaluru Tech Summit 2026', 'India\'s flagship technology event focusing on AI, Biotech, Startup ecosystems, and Deep Tech innovations.', 'Bangalore Palace, Bengaluru, Karnataka', '2026-07-22', '09:30:00', 'Admin', '1500', 'Tech', 'assets/images/placeholder-1.png', 'Approved', '1', '2026-07-21 23:30:36'),
('2', 'Mumbai Entrepreneurship Summit', 'Bringing together India\'s top venture capitalists, angel investors, and startup founders for a live pitching arena.', 'Taj Lands End, Bandra, Mumbai', '2026-07-22', '10:00:00', 'Admin', '600', 'Business', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:36'),
('3', 'Kala Ghoda Live Art Fest', 'A vibrant celebration of art, heritage, literature, and performances in the heart of Mumbai\'s historical art district.', 'Kala Ghoda Precinct, Fort, Mumbai', '2026-07-22', '11:00:00', 'Admin', '800', 'Art', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:36'),
('4', 'India Mobile Congress 2026', 'The largest digital technology forum in Asia showcasing 5G, 6G, IoT, and Next-Gen telecom breakthroughs.', 'Pragati Maidan, New Delhi', '2026-08-05', '10:00:00', 'Admin', '2000', 'Tech', 'assets/images/placeholder-1.png', 'Approved', '1', '2026-07-21 23:30:36'),
('5', 'PyCon India 2026', 'The premier conference in India for Python programmers, developers, researchers, and open-source advocates.', 'HICC, Hyderabad, Telangana', '2026-08-20', '09:00:00', 'Admin', '1200', 'Tech', 'assets/images/placeholder-1.png', 'Approved', '1', '2026-07-21 23:30:36'),
('6', 'DevOpsDays India', 'A community-led conference covering IT infrastructure, deployment automation, security, and developer culture.', 'NIMHANS Convention Centre, Bengaluru', '2026-09-04', '09:00:00', 'Admin', '500', 'Tech', 'assets/images/placeholder-1.png', 'Approved', '1', '2026-07-21 23:30:36'),
('7', 'JSConf India 2026', 'Dedicated to JavaScript, its ecosystems, frontend frameworks, WebAssembly, and modern web application designs.', 'Leela Ambience, Gurugram', '2026-09-19', '09:30:00', 'Admin', '700', 'Tech', 'assets/images/placeholder-1.png', 'Approved', '1', '2026-07-21 23:30:36'),
('8', 'Global FinTech Fest Mumbai', 'Discussing digital payments, open banking, blockchain compliance, and the future of global transaction systems.', 'Jio World Convention Centre, Mumbai', '2026-10-04', '10:00:00', 'Admin', '3000', 'Tech', 'assets/images/placeholder-1.png', 'Approved', '1', '2026-07-21 23:30:36'),
('9', 'AWS Summit Delhi', 'Join the cloud computing community to discover how cloud technology accelerates business and developer architectures.', 'Indira Gandhi Arena, New Delhi', '2026-10-19', '08:30:00', 'Admin', '2500', 'Tech', 'assets/images/placeholder-1.png', 'Approved', '1', '2026-07-21 23:30:36'),
('10', 'Google Cloud Day Mumbai', 'Explore the latest advancements in Google Cloud technology, Serverless, Big Data, and Enterprise AI engines.', 'Grand Hyatt, Mumbai, Maharashtra', '2026-11-03', '09:00:00', 'Admin', '1500', 'Tech', 'assets/images/placeholder-1.png', 'Approved', '1', '2026-07-21 23:30:36'),
('11', 'Vibrant Gujarat Global Summit', 'A premier business platform attracting global leaders, trade councils, policymakers, and corporate investors.', 'Mahatma Mandir, Gandhinagar, Gujarat', '2026-08-10', '09:30:00', 'Admin', '5000', 'Business', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:36'),
('12', 'Startup Mahakumbh Delhi', 'India\'s largest gathering of startups, venture builders, incubation hubs, and strategic corporate partners.', 'Bharat Mandapam, New Delhi', '2026-08-25', '10:00:00', 'Admin', '4000', 'Business', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:36'),
('13', 'TiEcon Delhi NCR', 'Focusing on building sustainable business models, fundraising rounds, and corporate governance for startups.', 'Lalit Hotel, New Delhi', '2026-09-09', '09:00:00', 'Admin', '1000', 'Business', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:36'),
('14', 'India Retail Forum', 'Connecting retail brands, e-commerce giants, supply chain logistics companies, and payment enablers.', 'The Westin, Mumbai Garden City', '2026-09-24', '10:00:00', 'Admin', '1200', 'Business', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:36'),
('15', 'Hyderabad Startup Expo', 'Showcasing deep tech, healthcare, agriculture, and SaaS innovations from South India\'s ecosystem.', 'T-Hub 2.0, Hyderabad, Telangana', '2026-10-09', '09:30:00', 'Admin', '1500', 'Business', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:36'),
('16', 'Pune Business Conclave', 'Exploring manufacturing automation, automotive designs, and financial frameworks for medium-scale enterprises.', 'JW Marriott, Pune, Maharashtra', '2026-10-24', '10:00:00', 'Admin', '500', 'Business', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:36'),
('17', 'India Art Fair Delhi 2026', 'The leading platform for modern and contemporary art from South Asia, featuring galleries, installations, and forums.', 'NSIC Exhibition Grounds, Okhla, New Delhi', '2026-08-12', '11:00:00', 'Admin', '3000', 'Art', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:36'),
('18', 'Kochi-Muziris Biennale', 'An international exhibition of contemporary art held in Kochi, showcasing international and local installations.', 'Aspinwall House, Fort Kochi, Kerala', '2026-08-30', '10:00:00', 'Admin', '4000', 'Art', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:36'),
('19', 'Serendipity Arts Festival Goa', 'A multi-disciplinary arts festival with craft displays, performance arts, culinary arts, and visual arts panels.', 'Panaji Heritage Zone, Goa', '2026-09-14', '12:00:00', 'Admin', '2000', 'Art', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:36'),
('20', 'Taj Mahotsav Agra', 'A 10-day cultural festival celebrating Indian crafts, arts, classical dances, and folk music near the Taj Mahal.', 'Shilpgram, Agra, Uttar Pradesh', '2026-09-29', '10:30:00', 'Admin', '1500', 'Art', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:36'),
('21', 'Delhi Street Art Fest', 'Live wall painting sessions, graffiti workshops, and urban art tours with renowned street artists.', 'Lodhi Art District, New Delhi', '2026-10-14', '14:00:00', 'Admin', '500', 'Art', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:36'),
('22', 'Sunburn Festival Goa 2026', 'Asia\'s largest Electronic Dance Music (EDM) festival, featuring global and national headline DJs.', 'Vagator Beach, Goa', '2026-08-15', '15:30:00', 'Admin', '10000', 'Music', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:36'),
('23', 'NH7 Weekender Pune', 'India\'s happiest music festival, celebrating indie music, rock, metal, hip-hop, and comedy across multiple stages.', 'Teerth Fields, Mahalunge, Pune', '2026-09-01', '15:00:00', 'Admin', '8000', 'Music', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:36'),
('24', 'Lollapalooza India Mumbai', 'A multi-genre international music festival bringing global rock, pop, and electronic acts to Mumbai.', 'Mahalaxmi Racecourse, Mumbai', '2026-09-21', '14:00:00', 'Admin', '15000', 'Music', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:36'),
('25', 'Mahindra Blues Festival', 'The largest blues music festival in Asia, gathering top international blues artists for a weekend soul celebration.', 'Mehboob Studios, Bandra, Mumbai', '2026-10-07', '17:00:00', 'Admin', '2000', 'Music', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:37'),
('26', 'Jodhpur RIFF 2026', 'Rajasthan International Folk Festival, showcasing folk artists, roots music, and international collaborative acts.', 'Mehrangarh Fort, Jodhpur, Rajasthan', '2026-10-21', '16:00:00', 'Admin', '1200', 'Music', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:37'),
('27', 'National Street Food Festival', 'A delicious festival bringing street food vendors from every state of India under one single roof.', 'Jawaharlal Nehru Stadium, New Delhi', '2026-08-02', '12:00:00', 'Admin', '5000', 'Food', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:37'),
('28', 'The Grub Fest Mumbai', 'India\'s premier food festival featuring popular restaurants, live cooking demos, organic markets, and live music.', 'Jio Garden, BKC, Mumbai', '2026-08-18', '13:00:00', 'Admin', '6000', 'Food', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:37'),
('29', 'Bengaluru Food Truck Fest', 'An outdoor event featuring custom food trucks, dessert panels, craft breweries, and live acoustic sessions.', 'Jayamahal Palace, Bengaluru', '2026-09-05', '12:30:00', 'Admin', '1500', 'Food', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:37'),
('30', 'Hyderabad Biryani Carnival', 'Celebrating the rich culinary history of Biryani with live competitions, masterclasses, and tasting stalls.', 'NTR Gardens, Hyderabad', '2026-09-25', '11:30:00', 'Admin', '4000', 'Food', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:37'),
('31', 'Amritsar Street Food Tour', 'A curated food walk and culinary expo celebrating Kulchas, Lassi, Jalebi, and historical Punjabi delicacies.', 'Golden Temple Plaza Precinct, Amritsar', '2026-10-13', '09:00:00', 'Admin', '800', 'Food', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:37'),
('32', 'Tata Mumbai Marathon 2026', 'The premier marathon in Asia, gathering international runners, fitness enthusiasts, and corporate teams.', 'Chhatrapati Shivaji Terminus (CST), Mumbai', '2026-08-08', '05:00:00', 'Admin', '15000', 'Sports', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:37'),
('33', 'Delhi Half Marathon', 'A scenic half-marathon race through the historical landmarks of New Delhi, promoting health and wellness.', 'Jawaharlal Nehru Stadium, Delhi', '2026-08-28', '06:00:00', 'Admin', '10000', 'Sports', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:37'),
('34', 'Bengaluru 10K Challenge', 'A competitive 10-kilometer road race designed to promote running culture and corporate wellness in Bengaluru.', 'Kanteerava Stadium, Bengaluru', '2026-09-11', '06:15:00', 'Admin', '5000', 'Sports', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:37'),
('35', 'Goa International Sailing Regatta', 'An international windsurfing and yacht sailing competition attracting aquatic athletes across the globe.', 'Dona Paula Beach, Panaji, Goa', '2026-09-27', '08:00:00', 'Admin', '300', 'Sports', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:37'),
('36', 'Himalayan MTB Cycling Expedition', 'A grueling mountain biking race through the steep terrains, valleys, and high mountain passes of Manali.', 'Mall Road, Manali, Himachal Pradesh', '2026-10-17', '07:00:00', 'Admin', '150', 'Sports', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:37'),
('37', 'Aero India Bengaluru 2026', 'Asia\'s premier air show and defense expo displaying aerobatic performances, aerospace designs, and radars.', 'Yelahanka Air Force Station, Bengaluru', '2026-08-14', '09:00:00', 'Admin', '8000', 'Science', 'assets/images/placeholder-1.png', 'Approved', '1', '2026-07-21 23:30:37'),
('38', 'Indian Science Congress', 'Meeting of scientific minds, space researchers, biochemists, and young student inventors across India.', 'Science City, Kolkata, West Bengal', '2026-09-03', '09:30:00', 'Admin', '3000', 'Science', 'assets/images/placeholder-1.png', 'Approved', '1', '2026-07-21 23:30:37'),
('39', 'Bengaluru Space Expo', 'International space exhibition focusing on satellite builds, launch vehicles, private space startups, and ISRO systems.', 'BIEC, Tumkur Road, Bengaluru', '2026-09-17', '10:00:00', 'Admin', '2000', 'Science', 'assets/images/placeholder-1.png', 'Approved', '1', '2026-07-21 23:30:37'),
('40', 'National Astronomy Summit', 'Live telescope sky gazing, astrophotography workshops, and planetarium talks on deep space galaxies.', 'IUCAA, Pune University, Pune', '2026-10-03', '18:00:00', 'Admin', '500', 'Science', 'assets/images/placeholder-1.png', 'Approved', '1', '2026-07-21 23:30:37'),
('41', 'India Artificial Intelligence Conclave', 'Exploring neural networks, robotics, machine translation systems, and industrial automation models.', 'IIIT, Hyderabad, Telangana', '2026-10-23', '09:00:00', 'Admin', '1200', 'Science', 'assets/images/placeholder-1.png', 'Approved', '1', '2026-07-21 23:30:37'),
('42', 'International Yoga Festival', 'A week-long spiritual and wellness festival featuring yoga gurus, meditation experts, and ayurveda doctors.', 'Parmarth Niketan Ashram, Rishikesh, Uttarakhand', '2026-08-16', '06:00:00', 'Admin', '2000', 'Health', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:37'),
('43', 'Global Health & Wellness Expo', 'Exhibition of organic foods, wellness technologies, diagnostics equipments, fitness brands, and dietitians.', 'Pragati Maidan, New Delhi', '2026-09-07', '10:00:00', 'Admin', '3000', 'Health', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:37'),
('44', 'Mumbai Medical & Healthcare Summit', 'A conference for surgeons, cardiologists, and biotech researchers reviewing modern robotic surgical tools.', 'St. Regis Hotel, Lower Parel, Mumbai', '2026-09-23', '09:30:00', 'Admin', '800', 'Health', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:37'),
('45', 'National Ayurveda Congress', 'Promoting ancient Indian medicine systems, herb research, and alternative health therapies.', 'Patanjali Yogpeeth, Haridwar', '2026-10-11', '08:00:00', 'Admin', '1500', 'Health', 'assets/images/placeholder-2.png', 'Approved', '1', '2026-07-21 23:30:37'),
('46', 'Jaipur Literature Festival 2026', 'The greatest literary show on Earth, gathering authors, Nobel laureates, poets, and critics for live talks.', 'Hotel Clarks Amer, Jaipur, Rajasthan', '2026-08-04', '09:30:00', 'Admin', '5000', 'Creative', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:37'),
('47', 'Bengaluru Design Week', 'A week-long celebration of UI/UX, product design, fashion, architecture, and visual graphics.', 'NID Campus, Bengaluru, Karnataka', '2026-08-22', '10:00:00', 'Admin', '1200', 'Creative', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:37'),
('48', 'Mumbai Film & VFX Conclave', 'Connecting movie directors, visual effects studios, animation creators, and film school students.', 'Film City, Goregaon, Mumbai', '2026-09-08', '11:00:00', 'Admin', '1000', 'Creative', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:37'),
('49', 'Delhi Photography Expo', 'Camera brand showcases, masterclasses on lighting and framing, and a global photo competition gallery.', 'Habitat Centre, New Delhi', '2026-09-26', '10:00:00', 'Admin', '700', 'Creative', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:37'),
('50', 'Goa Creative Writing Retreat', 'Interactive workshops, script writing mentoring, and book drafting sessions with seasoned novelists.', 'Fort Aguada Beach Resort, Candolim, Goa', '2026-10-15', '09:30:00', 'Admin', '100', 'Creative', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:37'),
('51', 'India Fashion & Textile Conclave', 'Showcasing sustainable cotton, handmade textiles, handloom weaves, and modern fashion ramp runways.', 'Adlux Exhibition Centre, Kochi, Kerala', '2026-10-27', '13:00:00', 'Admin', '1500', 'Creative', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:37'),
('52', 'Hyderabad Game Developers Arena', 'Showcasing indie game creators, console publishers, gaming tech, and interactive esports arenas.', 'HITEX Exhibition Center, Hyderabad', '2026-11-08', '10:00:00', 'Admin', '2000', 'Creative', 'assets/images/placeholder-3.png', 'Approved', '1', '2026-07-21 23:30:37');

-- --------------------------------------------------------
-- Table structure for table `registrations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `registrations`;
CREATE TABLE `registrations` (
  `Registration_ID` int NOT NULL AUTO_INCREMENT,
  `User_ID` int NOT NULL,
  `Event_ID` int NOT NULL,
  `Registration_Date` date NOT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Confirmed',
  `College_Organization` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Registration_ID`),
  UNIQUE KEY `unique_registration` (`User_ID`,`Event_ID`),
  KEY `idx_user` (`User_ID`),
  KEY `idx_event` (`Event_ID`),
  KEY `idx_status` (`Status`),
  CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`) ON DELETE CASCADE,
  CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`Event_ID`) REFERENCES `events` (`Event_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `registrations`
-- (No data to dump for table `registrations`)

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `User_ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Mobile` varchar(15) DEFAULT NULL,
  `Profile_Pic` varchar(255) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` varchar(20) NOT NULL DEFAULT 'Participant',
  `Account_Status` varchar(20) NOT NULL DEFAULT 'Approved',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`User_ID`),
  UNIQUE KEY `Email` (`Email`),
  KEY `idx_email` (`Email`),
  KEY `idx_role` (`Role`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `users`
INSERT INTO `users` (`User_ID`, `Name`, `Email`, `Mobile`, `Profile_Pic`, `Password`, `Role`, `Account_Status`, `created_at`) VALUES
('1', 'Admin', 'admin@eventreg.com', '9999999999', NULL, '$2y$10$zT57.RJNRMcASH3Ig7szAeQVeDpxVhT5dCIK8sBgp8jHDEomC6oE6', 'Admin', 'Approved', '2026-07-15 17:46:45'),
('2', 'Parth Santosh Tupe', 'xyz_123@gmail.com', '9849871969', NULL, '$2y$10$IASn4f235/Fx9kNhyQTO1ufFT5gRUW2uRwM2PSLA5Tr0OPxGsfmpm', 'Participant', 'Approved', '2026-07-16 12:12:15'),
('3', 'Gitesh Kene', 'happyevents@gmail.com', '9187649179', NULL, '$2y$10$p1iadyO8xZ8acEg29jkrhuqq9hbgqp2F3SdwJ4KEgcXNwlP0ncWrC', 'Organizer', 'Approved', '2026-07-16 19:40:34'),
('29', 'Parth Tupe FY', 'parthstupe@gmail.com', '8796235734', NULL, '$2y$10$svHUT4QGdzFPdVGA42o5lu.aXfo6IBBoRw7gpU0d7uFOIuOwsXmjC', 'Admin', 'Approved', '2026-07-21 10:45:08'),
('30', 'EventHub Support Admin', 'eventoraganizers2026@gmail.com', '9999999999', NULL, '$2y$10$QwgSHCXWKRVAALOtqgzhVeVwKLd1r/fGpffPAzXa77IvvTjBDneXS', 'Admin', 'Approved', '2026-07-21 10:56:41'),
('31', 'Max Karotra', 'allogins.work@gmail.com', '6578279258', NULL, '$2y$10$wD2tyzwm93rVUJkpzYS/FuPCYq47QEwYs4r9iV7JJ2SdJEFQkEoRe', 'Organizer', 'Approved', '2026-07-21 22:04:08');

SET FOREIGN_KEY_CHECKS = 1;
