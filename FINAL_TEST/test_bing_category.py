import os
from bing_image_downloader import downloader
downloader.download("Technology Conference Event photography high quality", limit=5, output_dir="test_category", adult_filter_off=False, force_replace=False, timeout=10, filter="photo")
