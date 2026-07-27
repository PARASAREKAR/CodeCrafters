import pymysql
import random

DB_HOST = "localhost"
DB_USER = "root"
DB_PASS = "CS@aids25"
DB_NAME = "event_registration_db"

try:
    connection = pymysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASS, database=DB_NAME)
    cursor = connection.cursor(pymysql.cursors.DictCursor)
except Exception as e:
    print(f"Failed to connect to MySQL: {e}")
    exit(1)

# Get all organizers
cursor.execute("SELECT User_ID, Name FROM users WHERE Role = 'Organizer'")
organizers = cursor.fetchall()
organizer_ids = [org['User_ID'] for org in organizers]

if not organizer_ids:
    print("No organizers found.")
    exit(1)

print(f"Found {len(organizers)} organizers: {organizer_ids}")

# Get all events
cursor.execute("SELECT Event_ID FROM events")
events = cursor.fetchall()

print(f"Distributing {len(events)} events among organizers...")

for i, event in enumerate(events):
    event_id = event['Event_ID']
    # Distribute evenly
    assigned_org = organizer_ids[i % len(organizer_ids)]
    
    # Make half of them paid (fee = 1.00)
    fee = 1.00 if i % 2 == 0 else 0.00
    
    cursor.execute("UPDATE events SET created_by = %s, Event_Fee = %s WHERE Event_ID = %s", (assigned_org, fee, event_id))

connection.commit()
cursor.close()
connection.close()

print("Events successfully distributed and fees set!")
