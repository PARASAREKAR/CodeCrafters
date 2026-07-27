import requests
from bs4 import BeautifulSoup
import re

def test_bing(query):
    url = f"https://www.bing.com/images/search?q={query.replace(' ', '+')}&form=HDRSC2"
    headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/91.0.4472.124 Safari/537.36'}
    response = requests.get(url, headers=headers)
    
    # Bing uses murl in a JSON payload in the anchor tags
    urls = re.findall(r'murl&quot;:&quot;(.*?)&quot;', response.text)
    print(f"Found {len(urls)} images for '{query}'.")
    if urls:
        print(f"First image: {urls[0]}")

test_bing("Bengaluru Tech Summit 2026")
test_bing("Mumbai Entrepreneurship Summit")
