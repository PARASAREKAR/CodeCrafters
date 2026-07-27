from duckduckgo_search import DDGS

ddgs = DDGS()
results = ddgs.images("Tech Conference photography high quality", max_results=3)
for r in results:
    print(r['image'])
