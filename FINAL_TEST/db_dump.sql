DROP TABLE IF EXISTS attendance;

CREATE TABLE `attendance` (
  `Attendance_ID` int NOT NULL AUTO_INCREMENT,
  `Registration_ID` int NOT NULL,
  `Status` enum('Present','Absent') NOT NULL DEFAULT 'Absent',
  `marked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Attendance_ID`),
  KEY `Registration_ID` (`Registration_ID`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`Registration_ID`) REFERENCES `registrations` (`Registration_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;




DROP TABLE IF EXISTS contact_messages;

CREATE TABLE `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO contact_messages VALUES("1","Parth S. Tupe","allogins.work@gmail.com","Review of EventHub","Your website is very nice.","2026-07-21 09:58:32");
INSERT INTO contact_messages VALUES("2","Parth Tupe FY - C - AD1336","parthstupe@gmail.com","adsfdx","adsdscdxvtfjytjudktldkt","2026-07-22 10:21:00");
INSERT INTO contact_messages VALUES("3","Parth Tupe FY - C - AD1336","parthstupe@gmail.com","adsfdx","adsdscdxvtfjytjudktldkt","2026-07-22 10:21:13");
INSERT INTO contact_messages VALUES("4","Santosh Tupe","santoshtupe.bni@gmail.com","Need Events details happening in New year Eve","Need Events details happening in New year Eve in Pune","2026-07-22 22:32:57");
INSERT INTO contact_messages VALUES("5","Santosh Tupe","santoshtupe.bni@gmail.com","Need Events details happening in New year Eve","Need Events details happening in New year Eve in Pune","2026-07-22 22:33:32");
INSERT INTO contact_messages VALUES("6","Tanvi Pataskar","tanvipataskar2012@gmail.com","abs","asd","2026-07-22 04:59:51");
INSERT INTO contact_messages VALUES("7","Tanvi Pataskar","tanvipataskar2012@gmail.com","hvsgh","nbsadh","2026-07-22 05:28:43");
INSERT INTO contact_messages VALUES("8","Tanvi Pataskar","tanvipataskar2012@gmail.com","hvsgh","nbsadh","2026-07-22 05:28:55");
INSERT INTO contact_messages VALUES("9","Tanvi Pataskar","tanvipataskar2012@gmail.com","hvsgh","nbsadh","2026-07-22 05:29:08");
INSERT INTO contact_messages VALUES("10","Tanvi Pataskar","tanvipataskar2012@gmail.com","hvsgh","nbsadh","2026-07-22 05:29:20");
INSERT INTO contact_messages VALUES("11","Tanvi Pataskar","tanvipataskar2012@gmail.com","hvsgh","nbsadh","2026-07-22 05:29:30");
INSERT INTO contact_messages VALUES("12","Tanvi Pataskar","tanvipataskar2012@gmail.com","hvsgh","nbsadh","2026-07-22 05:30:00");
INSERT INTO contact_messages VALUES("13","Tanvi Pataskar","tanvipataskar2012@gmail.com","hvsgh","nbsadh","2026-07-22 05:30:16");



DROP TABLE IF EXISTS events;

CREATE TABLE `events` (
  `Event_ID` int NOT NULL AUTO_INCREMENT,
  `Event_Name` varchar(100) NOT NULL,
  `Description` text,
  `Venue` varchar(100) DEFAULT NULL,
  `Event_Date` date NOT NULL,
  `Event_Time` time DEFAULT NULL,
  `Organizer` varchar(100) DEFAULT NULL,
  `Capacity` int NOT NULL DEFAULT '100',
  `Event_Fee` decimal(10,2) DEFAULT '0.00',
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

INSERT INTO events VALUES("1","Bengaluru Tech Summit 2026","India\'s flagship technology event focusing on AI, Biotech, Startup ecosystems, and Deep Tech innovations.","Bangalore Palace, Bengaluru, Karnataka","2026-07-22","09:30:00","Admin","100","1.00","Tech","assets/images/uploads/event_final_1_1785091664.jpg","Approved","31","2026-07-21 23:30:36");
INSERT INTO events VALUES("2","Mumbai Entrepreneurship Summit","Bringing together India\'s top venture capitalists, angel investors, and startup founders for a live pitching arena.","Taj Lands End, Bandra, Mumbai","2026-07-22","10:00:00","Admin","100","0.00","Business","assets/images/uploads/event_final_2_1785091565.jpg","Approved","31","2026-07-21 23:30:36");
INSERT INTO events VALUES("3","Kala Ghoda Live Art Fest","A vibrant celebration of art, heritage, literature, and performances in the heart of Mumbai\'s historical art district.","Kala Ghoda Precinct, Fort, Mumbai","2026-07-22","11:00:00","Admin","100","1.00","Art","assets/images/uploads/event_final_3_1785091550.jpg","Approved","34","2026-07-21 23:30:36");
INSERT INTO events VALUES("4","India Mobile Congress 2026","The largest digital technology forum in Asia showcasing 5G, 6G, IoT, and Next-Gen telecom breakthroughs.","Pragati Maidan, New Delhi","2026-08-05","10:00:00","Admin","100","0.00","Tech","assets/images/uploads/event_final_4_1785091666.jpg","Approved","31","2026-07-21 23:30:36");
INSERT INTO events VALUES("5","PyCon India 2026","The premier conference in India for Python programmers, developers, researchers, and open-source advocates.","HICC, Hyderabad, Telangana","2026-08-20","09:00:00","Admin","100","0.00","Tech","assets/images/uploads/event_final_5_1785091669.jpg","Approved","31","2026-07-21 23:30:36");
INSERT INTO events VALUES("6","DevOpsDays India","A community-led conference covering IT infrastructure, deployment automation, security, and developer culture.","NIMHANS Convention Centre, Bengaluru","2026-09-04","09:00:00","Admin","100","1.00","Tech","assets/images/uploads/event_final_6_1785091672.jpg","Approved","34","2026-07-21 23:30:36");
INSERT INTO events VALUES("7","JSConf India 2026","Dedicated to JavaScript, its ecosystems, frontend frameworks, WebAssembly, and modern web application designs.","Leela Ambience, Gurugram","2026-09-19","09:30:00","Admin","100","1.00","Tech","assets/images/uploads/event_final_7_1785091674.jpg","Approved","37","2026-07-21 23:30:36");
INSERT INTO events VALUES("8","Global FinTech Fest Mumbai","Discussing digital payments, open banking, blockchain compliance, and the future of global transaction systems.","Jio World Convention Centre, Mumbai","2026-10-04","10:00:00","Admin","100","0.00","Tech","assets/images/uploads/event_final_8_1785091676.jpg","Approved","31","2026-07-21 23:30:36");
INSERT INTO events VALUES("9","AWS Summit Delhi","Join the cloud computing community to discover how cloud technology accelerates business and developer architectures.","Indira Gandhi Arena, New Delhi","2026-10-19","08:30:00","Admin","100","0.00","Tech","assets/images/uploads/event_final_9_1785091679.jpg","Approved","31","2026-07-21 23:30:36");
INSERT INTO events VALUES("10","Google Cloud Day Mumbai","Explore the latest advancements in Google Cloud technology, Serverless, Big Data, and Enterprise AI engines.","Grand Hyatt, Mumbai, Maharashtra","2026-11-03","09:00:00","Admin","100","1.00","Tech","assets/images/uploads/event_final_10_1785091681.jpg","Approved","34","2026-07-21 23:30:36");
INSERT INTO events VALUES("11","Vibrant Gujarat Global Summit","A premier business platform attracting global leaders, trade councils, policymakers, and corporate investors.","Mahatma Mandir, Gandhinagar, Gujarat","2026-08-10","09:30:00","Admin","100","0.00","Business","assets/images/uploads/event_final_11_1785091569.jpg","Approved","37","2026-07-21 23:30:36");
INSERT INTO events VALUES("12","Startup Mahakumbh Delhi","India\'s largest gathering of startups, venture builders, incubation hubs, and strategic corporate partners.","Bharat Mandapam, New Delhi","2026-08-25","10:00:00","Admin","100","0.00","Business","assets/images/uploads/event_final_12_1785091571.jpg","Approved","37","2026-07-21 23:30:36");
INSERT INTO events VALUES("13","TiEcon Delhi NCR","Focusing on building sustainable business models, fundraising rounds, and corporate governance for startups.","Lalit Hotel, New Delhi","2026-09-09","09:00:00","Admin","100","1.00","Business","assets/images/uploads/event_final_13_1785091574.jpg","Approved","31","2026-07-21 23:30:36");
INSERT INTO events VALUES("14","India Retail Forum","Connecting retail brands, e-commerce giants, supply chain logistics companies, and payment enablers.","The Westin, Mumbai Garden City","2026-09-24","10:00:00","Admin","100","0.00","Business","assets/images/uploads/event_final_14_1785091576.jpg","Approved","37","2026-07-21 23:30:36");
INSERT INTO events VALUES("15","Hyderabad Startup Expo","Showcasing deep tech, healthcare, agriculture, and SaaS innovations from South India\'s ecosystem.","T-Hub 2.0, Hyderabad, Telangana","2026-10-09","09:30:00","Admin","100","0.00","Business","assets/images/uploads/event_final_15_1785091579.jpg","Approved","37","2026-07-21 23:30:36");
INSERT INTO events VALUES("16","Pune Business Conclave","Exploring manufacturing automation, automotive designs, and financial frameworks for medium-scale enterprises.","JW Marriott, Pune, Maharashtra","2026-10-24","10:00:00","Admin","100","1.00","Business","assets/images/uploads/event_final_16_1785091581.jpg","Approved","34","2026-07-21 23:30:36");
INSERT INTO events VALUES("17","India Art Fair Delhi 2026","The leading platform for modern and contemporary art from South Asia, featuring galleries, installations, and forums.","NSIC Exhibition Grounds, Okhla, New Delhi","2026-08-12","11:00:00","Admin","100","1.00","Art","assets/images/uploads/event_final_17_1785091553.jpg","Approved","37","2026-07-21 23:30:36");
INSERT INTO events VALUES("18","Kochi-Muziris Biennale","An international exhibition of contemporary art held in Kochi, showcasing international and local installations.","Aspinwall House, Fort Kochi, Kerala","2026-08-30","10:00:00","Admin","100","0.00","Art","assets/images/uploads/event_final_18_1785091555.jpg","Approved","31","2026-07-21 23:30:36");
INSERT INTO events VALUES("19","Serendipity Arts Festival Goa","A multi-disciplinary arts festival with craft displays, performance arts, culinary arts, and visual arts panels.","Panaji Heritage Zone, Goa","2026-09-14","12:00:00","Admin","100","1.00","Art","assets/images/uploads/event_final_19_1785091558.jpg","Approved","34","2026-07-21 23:30:36");
INSERT INTO events VALUES("20","Taj Mahotsav Agra","A 10-day cultural festival celebrating Indian crafts, arts, classical dances, and folk music near the Taj Mahal.","Shilpgram, Agra, Uttar Pradesh","2026-09-29","10:30:00","Admin","100","0.00","Art","assets/images/uploads/event_final_20_1785091560.jpg","Approved","37","2026-07-21 23:30:36");
INSERT INTO events VALUES("21","Delhi Street Art Fest","Live wall painting sessions, graffiti workshops, and urban art tours with renowned street artists.","Lodhi Art District, New Delhi","2026-10-14","14:00:00","Admin","100","1.00","Art","assets/images/uploads/event_final_21_1785091563.jpg","Approved","34","2026-07-21 23:30:36");
INSERT INTO events VALUES("22","Sunburn Festival Goa 2026","Asia\'s largest Electronic Dance Music (EDM) festival, featuring global and national headline DJs.","Vagator Beach, Goa","2026-08-15","15:30:00","Admin","100","1.00","Music","assets/images/uploads/event_final_22_1785091628.jpg","Approved","34","2026-07-21 23:30:36");
INSERT INTO events VALUES("23","NH7 Weekender Pune","India\'s happiest music festival, celebrating indie music, rock, metal, hip-hop, and comedy across multiple stages.","Teerth Fields, Mahalunge, Pune","2026-09-01","15:00:00","Admin","100","1.00","Music","assets/images/uploads/event_final_23_1785091630.jpg","Approved","34","2026-07-21 23:30:36");
INSERT INTO events VALUES("24","Lollapalooza India Mumbai","A multi-genre international music festival bringing global rock, pop, and electronic acts to Mumbai.","Mahalaxmi Racecourse, Mumbai","2026-09-21","14:00:00","Admin","100","0.00","Music","assets/images/uploads/event_final_24_1785091632.jpg","Approved","31","2026-07-21 23:30:36");
INSERT INTO events VALUES("25","Mahindra Blues Festival","The largest blues music festival in Asia, gathering top international blues artists for a weekend soul celebration.","Mehboob Studios, Bandra, Mumbai","2026-10-07","17:00:00","Admin","100","1.00","Music","assets/images/uploads/event_final_25_1785091635.jpg","Approved","34","2026-07-21 23:30:37");
INSERT INTO events VALUES("26","Jodhpur RIFF 2026","Rajasthan International Folk Festival, showcasing folk artists, roots music, and international collaborative acts.","Mehrangarh Fort, Jodhpur, Rajasthan","2026-10-21","16:00:00","Admin","100","1.00","Music","assets/images/uploads/event_final_26_1785091637.jpg","Approved","34","2026-07-21 23:30:37");
INSERT INTO events VALUES("27","National Street Food Festival","A delicious festival bringing street food vendors from every state of India under one single roof.","Jawaharlal Nehru Stadium, New Delhi","2026-08-02","12:00:00","Admin","100","0.00","Food","assets/images/uploads/event_final_27_1785091601.jpg","Approved","37","2026-07-21 23:30:37");
INSERT INTO events VALUES("28","The Grub Fest Mumbai","India\'s premier food festival featuring popular restaurants, live cooking demos, organic markets, and live music.","Jio Garden, BKC, Mumbai","2026-08-18","13:00:00","Admin","100","1.00","Food","assets/images/uploads/event_final_28_1785091605.jpg","Approved","31","2026-07-21 23:30:37");
INSERT INTO events VALUES("29","Bengaluru Food Truck Fest","An outdoor event featuring custom food trucks, dessert panels, craft breweries, and live acoustic sessions.","Jayamahal Palace, Bengaluru","2026-09-05","12:30:00","Admin","100","0.00","Food","assets/images/uploads/event_final_29_1785091608.jpg","Approved","31","2026-07-21 23:30:37");
INSERT INTO events VALUES("30","Hyderabad Biryani Carnival","Celebrating the rich culinary history of Biryani with live competitions, masterclasses, and tasting stalls.","NTR Gardens, Hyderabad","2026-09-25","11:30:00","Admin","100","1.00","Food","assets/images/uploads/event_final_30_1785091611.jpg","Approved","34","2026-07-21 23:30:37");
INSERT INTO events VALUES("31","Amritsar Street Food Tour","A curated food walk and culinary expo celebrating Kulchas, Lassi, Jalebi, and historical Punjabi delicacies.","Golden Temple Plaza Precinct, Amritsar","2026-10-13","09:00:00","Admin","100","0.00","Food","assets/images/uploads/event_final_31_1785091613.jpg","Approved","31","2026-07-21 23:30:37");
INSERT INTO events VALUES("32","Tata Mumbai Marathon 2026","The premier marathon in Asia, gathering international runners, fitness enthusiasts, and corporate teams.","Chhatrapati Shivaji Terminus (CST), Mumbai","2026-08-08","05:00:00","Admin","100","1.00","Sports","assets/images/uploads/event_final_32_1785091650.jpg","Approved","34","2026-07-21 23:30:37");
INSERT INTO events VALUES("33","Delhi Half Marathon","A scenic half-marathon race through the historical landmarks of New Delhi, promoting health and wellness.","Jawaharlal Nehru Stadium, Delhi","2026-08-28","06:00:00","Admin","100","1.00","Sports","assets/images/uploads/event_final_33_1785091653.jpg","Approved","37","2026-07-21 23:30:37");
INSERT INTO events VALUES("34","Bengaluru 10K Challenge","A competitive 10-kilometer road race designed to promote running culture and corporate wellness in Bengaluru.","Kanteerava Stadium, Bengaluru","2026-09-11","06:15:00","Admin","100","0.00","Sports","assets/images/uploads/event_final_34_1785091656.jpg","Approved","31","2026-07-21 23:30:37");
INSERT INTO events VALUES("35","Goa International Sailing Regatta","An international windsurfing and yacht sailing competition attracting aquatic athletes across the globe.","Dona Paula Beach, Panaji, Goa","2026-09-27","08:00:00","Admin","100","1.00","Sports","assets/images/uploads/event_final_35_1785091658.jpg","Approved","34","2026-07-21 23:30:37");
INSERT INTO events VALUES("36","Himalayan MTB Cycling Expedition","A grueling mountain biking race through the steep terrains, valleys, and high mountain passes of Manali.","Mall Road, Manali, Himachal Pradesh","2026-10-17","07:00:00","Admin","100","1.00","Sports","assets/images/uploads/event_final_36_1785091660.jpg","Approved","31","2026-07-21 23:30:37");
INSERT INTO events VALUES("37","Aero India Bengaluru 2026","Asia\'s premier air show and defense expo displaying aerobatic performances, aerospace designs, and radars.","Yelahanka Air Force Station, Bengaluru","2026-08-14","09:00:00","Admin","100","0.00","Science","assets/images/uploads/event_final_37_1785091639.jpg","Approved","31","2026-07-21 23:30:37");
INSERT INTO events VALUES("38","Indian Science Congress","Meeting of scientific minds, space researchers, biochemists, and young student inventors across India.","Science City, Kolkata, West Bengal","2026-09-03","09:30:00","Admin","100","0.00","Science","assets/images/uploads/event_final_38_1785091641.jpg","Approved","37","2026-07-21 23:30:37");
INSERT INTO events VALUES("39","Bengaluru Space Expo","International space exhibition focusing on satellite builds, launch vehicles, private space startups, and ISRO systems.","BIEC, Tumkur Road, Bengaluru","2026-09-17","10:00:00","Admin","100","0.00","Science","assets/images/uploads/event_final_39_1785091644.jpg","Approved","37","2026-07-21 23:30:37");
INSERT INTO events VALUES("40","National Astronomy Summit","Live telescope sky gazing, astrophotography workshops, and planetarium talks on deep space galaxies.","IUCAA, Pune University, Pune","2026-10-03","18:00:00","Admin","100","1.00","Science","assets/images/uploads/event_final_40_1785091646.jpg","Approved","34","2026-07-21 23:30:37");
INSERT INTO events VALUES("41","India Artificial Intelligence Conclave","Exploring neural networks, robotics, machine translation systems, and industrial automation models.","IIIT, Hyderabad, Telangana","2026-10-23","09:00:00","Admin","100","0.00","Science","assets/images/uploads/event_final_41_1785091648.jpg","Approved","37","2026-07-21 23:30:37");
INSERT INTO events VALUES("42","International Yoga Festival","A week-long spiritual and wellness festival featuring yoga gurus, meditation experts, and ayurveda doctors.","Parmarth Niketan Ashram, Rishikesh, Uttarakhand","2026-08-16","06:00:00","Admin","100","0.00","Health","assets/images/uploads/event_final_42_1785091618.jpg","Approved","37","2026-07-21 23:30:37");
INSERT INTO events VALUES("43","Global Health & Wellness Expo","Exhibition of organic foods, wellness technologies, diagnostics equipments, fitness brands, and dietitians.","Pragati Maidan, New Delhi","2026-09-07","10:00:00","Admin","100","1.00","Health","assets/images/uploads/event_final_43_1785091620.jpg","Approved","34","2026-07-21 23:30:37");
INSERT INTO events VALUES("44","Mumbai Medical & Healthcare Summit","A conference for surgeons, cardiologists, and biotech researchers reviewing modern robotic surgical tools.","St. Regis Hotel, Lower Parel, Mumbai","2026-09-23","09:30:00","Admin","100","1.00","Health","assets/images/uploads/event_final_44_1785091622.jpg","Approved","34","2026-07-21 23:30:37");
INSERT INTO events VALUES("45","National Ayurveda Congress","Promoting ancient Indian medicine systems, herb research, and alternative health therapies.","Patanjali Yogpeeth, Haridwar","2026-10-11","08:00:00","Admin","100","1.00","Health","assets/images/uploads/event_final_45_1785091625.jpg","Approved","37","2026-07-21 23:30:37");
INSERT INTO events VALUES("46","Jaipur Literature Festival 2026","The greatest literary show on Earth, gathering authors, Nobel laureates, poets, and critics for live talks.","Hotel Clarks Amer, Jaipur, Rajasthan","2026-08-04","09:30:00","Admin","100","1.00","Creative","assets/images/uploads/event_final_46_1785091583.jpg","Approved","31","2026-07-21 23:30:37");
INSERT INTO events VALUES("47","Bengaluru Design Week","A week-long celebration of UI/UX, product design, fashion, architecture, and visual graphics.","NID Campus, Bengaluru, Karnataka","2026-08-22","10:00:00","Admin","100","1.00","Creative","assets/images/uploads/event_final_47_1785091586.jpg","Approved","34","2026-07-21 23:30:37");
INSERT INTO events VALUES("48","Mumbai Film & VFX Conclave","Connecting movie directors, visual effects studios, animation creators, and film school students.","Film City, Goregaon, Mumbai","2026-09-08","11:00:00","Admin","100","0.00","Creative","assets/images/uploads/event_final_48_1785091588.jpg","Approved","37","2026-07-21 23:30:37");
INSERT INTO events VALUES("49","Delhi Photography Expo","Camera brand showcases, masterclasses on lighting and framing, and a global photo competition gallery.","Habitat Centre, New Delhi","2026-09-26","10:00:00","Admin","100","0.00","Creative","assets/images/uploads/event_final_49_1785091591.jpg","Approved","31","2026-07-21 23:30:37");
INSERT INTO events VALUES("50","Goa Creative Writing Retreat","Interactive workshops, script writing mentoring, and book drafting sessions with seasoned novelists.","Fort Aguada Beach Resort, Candolim, Goa","2026-10-15","09:30:00","Admin","100","0.00","Creative","assets/images/uploads/event_final_50_1785091593.jpg","Approved","37","2026-07-21 23:30:37");
INSERT INTO events VALUES("51","India Fashion & Textile Conclave","Showcasing sustainable cotton, handmade textiles, handloom weaves, and modern fashion ramp runways.","Adlux Exhibition Centre, Kochi, Kerala","2026-10-27","13:00:00","Admin","100","0.00","Creative","assets/images/uploads/event_final_51_1785091595.jpg","Approved","31","2026-07-21 23:30:37");
INSERT INTO events VALUES("52","Hyderabad Game Developers Arena","Showcasing indie game creators, console publishers, gaming tech, and interactive esports arenas.","HITEX Exhibition Center, Hyderabad","2026-11-08","10:00:00","Admin","100","0.00","Creative","assets/images/uploads/event_final_52_1785091597.jpg","Approved","37","2026-07-21 23:30:37");



DROP TABLE IF EXISTS payments;

CREATE TABLE `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `registration_id` int NOT NULL,
  `qr_token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qr_viewed_count` int DEFAULT '0',
  `amount` decimal(10,2) DEFAULT '0.00',
  `status` enum('Pending','Paid','Cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `qr_token` (`qr_token`),
  KEY `registration_id` (`registration_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`Registration_ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO payments VALUES("1","9","9674592206372a60d8986d109019237e5a9101ab86e200420ec6c0a2e83fd001","0","1.00","Pending","","2026-07-27 10:43:10");
INSERT INTO payments VALUES("2","8","75037bc516471a106547a60e62adc532a2630865b71940f7db9b435deca99456","0","1.00","Pending","","2026-07-27 10:43:31");
INSERT INTO payments VALUES("3","11","46e8f4479e9d2178e4c41a16ec59122fff473392ae78e8b5509dfada442dd6ec","0","1.00","Pending","","2026-07-27 11:11:16");
INSERT INTO payments VALUES("4","10","46b367cef6c64649ab5c899b3edf310469298519dad465a205c458b0c560070c","0","1.00","Pending","","2026-07-27 11:12:00");



DROP TABLE IF EXISTS registrations;

CREATE TABLE `registrations` (
  `Registration_ID` int NOT NULL AUTO_INCREMENT,
  `User_ID` int NOT NULL,
  `Event_ID` int NOT NULL,
  `Registration_Date` date NOT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'Confirmed',
  `organizer_approved` tinyint(1) DEFAULT '0',
  `College_Organization` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Registration_ID`),
  UNIQUE KEY `unique_registration` (`User_ID`,`Event_ID`),
  KEY `idx_user` (`User_ID`),
  KEY `idx_event` (`Event_ID`),
  KEY `idx_status` (`Status`),
  CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`) ON DELETE CASCADE,
  CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`Event_ID`) REFERENCES `events` (`Event_ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO registrations VALUES("1","32","3","2026-07-22","Confirmed","0","Zeal College of Engineering and Research","2026-07-22 09:27:43");
INSERT INTO registrations VALUES("2","35","20","2026-07-22","Confirmed","0","","2026-07-22 22:21:26");
INSERT INTO registrations VALUES("3","35","36","2026-07-22","Confirmed","0","","2026-07-22 22:42:09");
INSERT INTO registrations VALUES("4","35","27","2026-07-27","Confirmed","0","Zeal College of Engineering and Research","2026-07-27 00:40:58");
INSERT INTO registrations VALUES("5","35","1","2026-07-27","Confirmed","0","Zeal College","2026-07-27 00:41:46");
INSERT INTO registrations VALUES("6","35","46","2026-07-27","Confirmed","0","","2026-07-27 01:18:45");
INSERT INTO registrations VALUES("7","32","46","2026-07-27","Confirmed","0","Zeal College","2026-07-27 09:22:21");
INSERT INTO registrations VALUES("8","32","28","2026-07-27","Confirmed","1","","2026-07-27 10:18:00");
INSERT INTO registrations VALUES("9","32","1","2026-07-27","Confirmed","1","","2026-07-27 10:27:54");
INSERT INTO registrations VALUES("10","36","46","2026-07-27","Confirmed","1","","2026-07-27 11:09:24");
INSERT INTO registrations VALUES("11","36","1","2026-07-27","Confirmed","1","","2026-07-27 11:10:06");



DROP TABLE IF EXISTS users;

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
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO users VALUES("1","Admin","admin@eventreg.com","9999999999","","$2y$10$zT57.RJNRMcASH3Ig7szAeQVeDpxVhT5dCIK8sBgp8jHDEomC6oE6","Admin","Approved","2026-07-15 17:46:45");
INSERT INTO users VALUES("2","Parth Santosh Tupe","xyz_123@gmail.com","9849871969","","$2y$10$IASn4f235/Fx9kNhyQTO1ufFT5gRUW2uRwM2PSLA5Tr0OPxGsfmpm","Participant","Approved","2026-07-16 12:12:15");
INSERT INTO users VALUES("3","Gitesh Kene","happyevents@gmail.com","9187649179","","$2y$10$p1iadyO8xZ8acEg29jkrhuqq9hbgqp2F3SdwJ4KEgcXNwlP0ncWrC","Organizer","Approved","2026-07-16 19:40:34");
INSERT INTO users VALUES("29","Parth Tupe FY","parthstupe@gmail.com","8796235734","","$2y$10$svHUT4QGdzFPdVGA42o5lu.aXfo6IBBoRw7gpU0d7uFOIuOwsXmjC","Admin","Approved","2026-07-21 10:45:08");
INSERT INTO users VALUES("30","EventHub Support Admin","eventoraganizers2026@gmail.com","9999999999","","$2y$10$QwgSHCXWKRVAALOtqgzhVeVwKLd1r/fGpffPAzXa77IvvTjBDneXS","Admin","Approved","2026-07-21 10:56:41");
INSERT INTO users VALUES("31","Max Karotra","allogins.work@gmail.com","6578279258","","$2y$10$wD2tyzwm93rVUJkpzYS/FuPCYq47QEwYs4r9iV7JJ2SdJEFQkEoRe","Organizer","Approved","2026-07-21 22:04:08");
INSERT INTO users VALUES("32","ayush sanjay chavhan","ayushchavhan6999@gmail.com","7770029774","","$2y$10$tVBtjkIgRRRxQeHS/jcJ0.deb9UecLmAJWseo4NrhZe.1qq0nk3dm","Participant","Approved","2026-07-22 09:22:55");
INSERT INTO users VALUES("33","jamir sayyed","jamirsayyed05@gmail.com","8624959460","assets/images/uploads/profile_pics/6e829b7c390325afc78329a3106d87c5.png","$2y$10$.Qst2vKXG24qT0uKy.Qq7Op0NqLmi29I/Oz7iOfc05WsrOvETJA3G","Participant","Approved","2026-07-22 09:32:33");
INSERT INTO users VALUES("34","Prafull Bugadikattekar","prafullbugadikattekar@gmail.com","5555555555","","$2y$10$ReDNALfjfUGijl/bdeoNkOjvK.1UtGBAjEzuLqAq5DiRRsMeu8MV6","Organizer","Approved","2026-07-22 10:59:43");
INSERT INTO users VALUES("35","Santosh Tupe","santoshtupe.bni@gmail.com","9619633375","","$2y$10$u.23r/aQ4fmDLbaZ0USV0OzAAbidOsNRRIXncUhXKvEZky3WaQY0i","Participant","Approved","2026-07-22 22:18:47");
INSERT INTO users VALUES("36","jagruti patil","jagupatil1020@gmail.com","9488372498","","$2y$10$eFPsiI4o/e.PIg52L8xgi.ds8OJ9sfkSonXtkjirGCmMR2J/gR3SC","Participant","Approved","2026-07-18 06:20:20");
INSERT INTO users VALUES("37","Tanvi Pataskar","tanvipataskar2012@gmail.com","7878429597","","$2y$10$fusuh7vSo1hkFMoHltFKh.D1nlAOsKnYNuKXbih/CX345I0sqNfae","Organizer","Approved","2026-07-18 11:45:12");
INSERT INTO users VALUES("38","Brinda","tanvipataskar1122@gmail.com","9598427956","","$2y$10$4pIvo7gaZ8a4L.U/xBLBcuJkYzUdDiVuEZCIMoMA4xzefGygkiAwK","Participant","Approved","2026-07-22 09:10:24");
INSERT INTO users VALUES("39","samarth pataskar","sureshpataskar5@gmail.com","7765387490","","$2y$10$zjzFH//MUQqkrRiZ66n9uOjENI81tB0w6x7lBXST7TKOBLXJHnCqO","Participant","Approved","2026-07-22 09:22:07");
INSERT INTO users VALUES("40","sakshi Gupta","sakshigupta@1212gmail.com","7991827447","","$2y$10$RHvlmPUZxYCKgAA2bkrDPeC9meDni5h0VdwMWQbp1mYItspCSsPSm","Participant","Approved","2026-07-24 11:48:09");
INSERT INTO users VALUES("41","Test User","testuser@example.com","9876543210","","$2y$10$U8xB1a9urvV3zlinqRgpR.N6dY.pBCrIRDw/EKzPSZpMW6bl7tSOq","Participant","Approved","2026-07-28 09:51:58");



