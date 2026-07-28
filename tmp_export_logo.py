import fitz
from PIL import Image
import io
from pathlib import Path

pdf_path = Path(r"C:\Users\diret\Downloads\LOGO GS ATUALIZADA.pdf")
out_png = Path(r"d:\xampp8212\htdocs\Sistemas\gruposorrisos\assets\img\logo-grupo-sorrisos.png")

doc = fitz.open(pdf_path)
page = doc[0]
print("page size:", page.rect)

# High DPI render (~432 DPI)
mat = fitz.Matrix(6, 6)
pix = page.get_pixmap(matrix=mat, alpha=True)
print("pix:", pix.width, pix.height, "n:", pix.n)

img = Image.open(io.BytesIO(pix.tobytes("png"))).convert("RGBA")
print("img mode/size:", img.mode, img.size)

pixels = img.load()
w, h = img.size
changed = 0
for y in range(h):
    for x in range(w):
        r, g, b, a = pixels[x, y]
        # Remove light gray / off-white PDF background
        if r >= 230 and g >= 230 and b >= 230:
            pixels[x, y] = (r, g, b, 0)
            changed += 1
        elif abs(r - g) < 12 and abs(g - b) < 12 and r >= 205:
            pixels[x, y] = (r, g, b, 0)
            changed += 1

bbox = img.getbbox()
print("bbox:", bbox, "cleared:", changed)
if bbox:
    pad = 24
    left = max(0, bbox[0] - pad)
    top = max(0, bbox[1] - pad)
    right = min(w, bbox[2] + pad)
    bottom = min(h, bbox[3] + pad)
    img = img.crop((left, top, right, bottom))

img.save(out_png, "PNG", optimize=True)
print("saved", out_png, img.size, out_png.stat().st_size)
doc.close()
