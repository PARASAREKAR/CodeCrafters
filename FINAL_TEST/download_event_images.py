import pymysql
import os
import shutil
import glob
from bing_image_downloader import downloader

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

cursor.execute("SELECT Event_ID, Event_Name, Event_Category FROM events")
events = cursor.fetchall()
print(f"Found {len(events)} events to process.")

for event in events:
    event_id = event['Event_ID']
    event_name = event['Event_Name']
    
    clean_name = event_name.replace('2026', '').strip()
    query = f"{clean_name} {event['Event_Category']} event"
    print(f"[{event_id}] Downloading 1 image for '{query}'...")
    
    # Download 1 image to a temp folder named after the query
    temp_dir = "temp_bing_images"
    try:
        downloader.download(query, limit=1, output_dir=temp_dir, adult_filter_off=False, force_replace=False, timeout=10, verbose=False)
        
        # Find the downloaded file
        query_dir = os.path.join(temp_dir, query)
        files = glob.glob(os.path.join(query_dir, "*"))
        if files:
            img_file = files[0]
            ext = os.path.splitext(img_file)[1]
            if not ext: ext = ".jpg"
            
            new_filename = f"event_real_{event_id}{ext}"
            new_filepath = os.path.join(UPLOAD_DIR, new_filename)
            
            # Copy file to uploads folder
            shutil.copy(img_file, new_filepath)
            
            # Update DB
            db_path = f"assets/images/uploads/{new_filename}"
            cursor.execute("UPDATE events SET Image_Path = %s WHERE Event_ID = %s", (db_path, event_id))
            connection.commit()
            print(f"[{event_id}] Success: {new_filename}")
        else:
            print(f"[{event_id}] Failed: No file downloaded.")
            
    except Exception as e:
        print(f"[{event_id}] Error: {e}")

cursor.close()
connection.close()

# Cleanup temp dir
if os.path.exists("temp_bing_images"):
    shutil.rmtree("temp_bing_images")

print("Finished replacing all images!")
