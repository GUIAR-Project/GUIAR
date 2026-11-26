import { expect, test } from '@playwright/test';

test('home apresenta navegação e abre o formulário de contato', async ({ page }) => {
  await page.goto('/index.html');

  await expect(page).toHaveTitle('GUIAR');
  await expect(page.locator('nav a', { hasText: 'Administrador' })).toHaveAttribute(
    'href',
    'routes.php?action=loginEmpresa'
  );
  await expect(page.locator('#contato-abrir')).toBeVisible();

  await page.locator('#contato-abrir').click();
  await expect(page.locator('#contato')).toBeVisible();
  await expect(page.locator('#contato-abrir')).toHaveAttribute('aria-expanded', 'true');
  await expect(page.locator('#home-contato-form')).toBeVisible();

  await page.locator('#home-contato-nome').fill('Teste CI');
  await page.locator('#home-contato-email').fill('teste@example.com');
  await page.locator('#home-contato-mensagem').fill('Mensagem de teste do frontend.');
  await expect(page.locator('#contato-contador')).toHaveText('30 / 5.000 caracteres');
  await expect(page.locator('#home-contato-enviar')).toBeEnabled();

  await page.locator('#contato-fechar').click();
  await expect(page.locator('#contato')).toBeHidden();
  await expect(page.locator('#contato-abrir')).toHaveAttribute('aria-expanded', 'false');

  await page.locator('#contato-abrir').click();
  await page.locator('#contato-fechar').click();
  await expect(page.locator('#contato')).toBeHidden();
});

test('páginas de acesso e recuperação usam os links da home', async ({ page }) => {
  const linksOnHome = async () => page.locator('#navbar .nav-link').evaluateAll(links =>
    links.map(link => {
      const url = new URL(link.href);
      return [link.textContent.trim(), `${url.pathname}${url.search}${url.hash}`];
    })
  );

  await page.goto('/index.html', { waitUntil: 'domcontentloaded' });
  const homeLinks = await linksOnHome();
  const expectedLinks = homeLinks.filter(([label]) => label !== 'Sobre nós');
  expect(homeLinks.some(([label]) => label === 'Sobre nós')).toBe(true);

  for (const path of [
    '/routes.php?action=loginEmpresa',
    '/routes.php?action=loginEntregador',
    '/routes.php?action=esqueceuSenha',
    '/routes.php?action=esqueceuSenhaEntregador'
  ]) {
    await page.goto(path, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#navbar .nav-link')).toHaveCount(expectedLinks.length);
    await expect(page.locator('#navbar .nav-link').first()).toBeVisible();
    await expect(page.locator('#navbar')).not.toContainText('Sobre nós');
    expect(await linksOnHome()).toEqual(expectedLinks);
  }

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/routes.php?action=esqueceuSenha', { waitUntil: 'domcontentloaded' });
  await page.getByRole('button', { name: 'Abrir menu' }).click();
  await expect(page.locator('#menu')).toBeVisible();
  await expect(page.locator('#menu .nav-link')).toHaveCount(expectedLinks.length);
});

test('login do administrador mantém o tamanho do login do entregador sem cortar o cadastro', async ({ page }) => {
  await page.goto('/routes.php?action=loginEmpresa', { waitUntil: 'domcontentloaded' });

  await expect(page.getByRole('link', { name: 'Esqueceu sua senha?' })).toHaveAttribute(
    'href',
    new URL('/routes.php?action=esqueceuSenha', page.url()).href
  );
  const signupLink = page.getByRole('link', { name: 'Cadastre a empresa e o primeiro administrador' });
  await expect(signupLink).toBeVisible();

  const card = page.locator('body > .flex-grow > div').first();
  const cardBox = await card.boundingBox();
  const signupBox = await signupLink.boundingBox();
  expect(cardBox).not.toBeNull();
  expect(signupBox).not.toBeNull();
  expect(signupBox.y + signupBox.height).toBeLessThanOrEqual(cardBox.y + cardBox.height);

  await page.goto('/routes.php?action=loginEntregador', { waitUntil: 'domcontentloaded' });
  const courierCardBox = await page.locator('body > .flex-grow > div').first().boundingBox();
  expect(courierCardBox).not.toBeNull();
  expect(cardBox.width).toBe(courierCardBox.width);
  expect(cardBox.height).toBe(courierCardBox.height);

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/routes.php?action=loginEmpresa', { waitUntil: 'domcontentloaded' });
  const mobileCardBox = await page.locator('body > .flex-grow > div').first().boundingBox();
  const mobileSignupBox = await page.getByRole('link', { name: 'Cadastre a empresa e o primeiro administrador' }).boundingBox();
  expect(mobileCardBox).not.toBeNull();
  expect(mobileSignupBox).not.toBeNull();
  expect(mobileSignupBox.y + mobileSignupBox.height).toBeLessThanOrEqual(mobileCardBox.y + mobileCardBox.height);
});

test('cadastro aplica as cores da logo selecionada e envia o campo esperado', async ({ page }) => {
  await page.goto('/routes.php?action=cadastroEmpresa', { waitUntil: 'domcontentloaded' });

  const logo = Buffer.from(
    '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="#c01450"/></svg>'
  );
  await page.locator('#formFile').setInputFiles({
    name: 'logo.svg',
    mimeType: 'image/svg+xml',
    buffer: logo
  });

  await expect(page.locator('#empresaPalettePreview')).toBeVisible();
  await expect(page.locator('#empresaPalettePreview')).toContainText('Principal: #C81450');
  await expect(page.locator('#botao')).toHaveCSS('background-color', 'rgb(200, 20, 80)');
  await expect(page.locator('#formFile')).toHaveAttribute('name', 'foto_logo');
  await expect(page.locator('#formFile')).toHaveAttribute('accept', 'image/*');
});
