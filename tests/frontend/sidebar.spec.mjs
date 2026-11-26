import { expect, test } from '@playwright/test';

test('menu lateral mobile abre e fecha pelo botão, overlay e tecla Escape', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/index.html', { waitUntil: 'domcontentloaded' });
  await page.setContent(`
    <style>
      .hidden { display: none; }
      #overlay.active { display: block !important; position: fixed; inset: 0; z-index: 40; }
      #sidebar.mobile-open { position: fixed; z-index: 50; }
    </style>
    <button id="toggleSidebar" aria-expanded="false">Abrir menu</button>
    <div id="overlay" class="hidden"></div>
    <aside id="sidebar">
      <button id="closeSidebar">Fechar menu</button>
    </aside>
  `);
  await page.addScriptTag({ url: '/public/js/mobile-sidebar.js' });

  const sidebar = page.locator('#sidebar');
  const overlay = page.locator('#overlay');
  const toggleButton = page.locator('#toggleSidebar');

  await expect(overlay).toBeHidden();
  await toggleButton.click();
  await expect(sidebar).toHaveClass(/mobile-open/);
  await expect(overlay).toBeVisible();
  await expect(toggleButton).toHaveAttribute('aria-expanded', 'true');

  await page.locator('#closeSidebar').click();
  await expect(sidebar).not.toHaveClass(/mobile-open/);
  await expect(overlay).toBeHidden();

  await toggleButton.click();
  await overlay.click();
  await expect(sidebar).not.toHaveClass(/mobile-open/);
  await expect(overlay).toBeHidden();

  await toggleButton.click();
  await page.keyboard.press('Escape');
  await expect(sidebar).not.toHaveClass(/mobile-open/);
  await expect(toggleButton).toHaveAttribute('aria-expanded', 'false');
});
