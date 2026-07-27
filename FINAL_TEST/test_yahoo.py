import requests
from bs4 import BeautifulSoup

def test_yahoo(query):
    url = f"https://images.search.yahoo.com/search/images?p={query.replace(' ', '+')}"
    headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}
    response = requests.get(url, headers=headers)
    soup = BeautifulSoup(response.text, 'html.parser')
    imgs = soup.find_all('img')
    found = []
    for img in imgs:
        src = img.get('data-src') or img.get('src')
        if src and src.startswith('http') and 'yimg.com' in src:
            found.append(src)
    print(f"Found {len(found)} images for {query}. First: {found[0] if found else 'None'}")

test_yahoo("Aero India")
test_yahoo("Mumbai Tech Fest")
