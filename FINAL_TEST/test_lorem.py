import requests
import time
import os

os.makedirs("test_lorem", exist_ok=True)
for i in range(5):
    # Using random parameter to avoid caching
    url = f"https://loremflickr.com/800/600/tech,conference/all?random={i}"
    r = requests.get(url, allow_redirects=True)
    if r.status_code == 200:
        with open(f"test_lorem/img_{i}.jpg", "wb") as f:
            f.write(r.content)
        print(f"Downloaded img_{i}.jpg")
    time.sleep(1)
