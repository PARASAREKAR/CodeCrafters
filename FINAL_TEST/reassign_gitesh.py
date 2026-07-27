import pymysql

DB_HOST = "localhost"
DB_USER = "root"
DB_PASS = "CS@aids25"
DB_NAME = "event_registration_db"

connection = pymysql.connect(host=DB_HOST, user=DB_USER, password=DB_PASS, database=DB_NAME, cursorclass=pymysql.cursors.DictCursor)
cursor = connection.cursor()

# Get events owned by 3
cursor.execute("SELECT Event_ID FROM events WHERE created_by = 3")
events = cursor.fetchall()

other_orgs = [31, 34, 37]

for i, event in enumerate(events):
    new_org = other_orgs[i % len(other_orgs)]
    cursor.execute("UPDATE events SET created_by = %s WHERE Event_ID = %s", (new_org, event['Event_ID']))

connection.commit()
cursor.close()
connection.close()

print("Reassigned Gitesh's events.")
