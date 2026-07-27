import pymysql
import requests
import os
import time

DB_HOST = "localhost"
DB_USER = "root"
DB_PASS = "CS@aids25"
DB_NAME = "event_registration_db"
UPLOAD_DIR = "assets/images/uploads"

try:
    connection = pymysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASS, database=DB_NAME)
    cursor = connection.cursor(pymysql.cursors.DictCursor)
except Exception as e:
    print(f"Failed to connect to MySQL: {e}")
    exit(1)

cursor.execute("SELECT Event_ID, Event_Category FROM events")
events = cursor.fetchall()
print(f"Found {len(events)} events to process.")

# Map categories to highly reliable Flickr tags to guarantee good images (and no cats!)
category_tags = {
    'Tech': 'conference,technology',
    'Music': 'concert,music',
    'Food': 'food,festival',
    'Sports': 'sports,stadium',
    'Science': 'science,laboratory',
    'Health': 'yoga,wellness',
    'Creative': 'art,gallery'
}

for i, event in enumerate(events):
    event_id = event['Event_ID']
    category = event['Event_Category']
    tags = category_tags.get(category, 'event,people')
    
    # We use random={event_id} to ensure we get a unique image per event
    url = f"https://loremflickr.com/800/600/{tags}/all?random={event_id}"
    print(f"[{event_id}] Fetching {category} image...")
    
    try:
        r = requests.get(url, allow_redirects=True, timeout=10)
        if r.status_code == 200:
            filename = f"event_final_{event_id}_{int(time.time())}.jpg"
            filepath = os.path.join(UPLOAD_DIR, filename)
            
            with open(filepath, 'wb') as f:
                f.write(r.content)
                
            db_path = f"assets/images/uploads/{filename}"
            cursor.execute("UPDATE events SET Image_Path = %s WHERE Event_ID = %s", (db_path, event_id))
            connection.commit()
            print(f"[{event_id}] Saved {filename}")
        else:
            print(f"[{event_id}] Failed with status {r.status_code}")
    except Exception as e:
        print(f"[{event_id}] Error: {e}")
        
    time.sleep(1)

cursor.close()
connection.close()
print("Finished replacing all images!")
