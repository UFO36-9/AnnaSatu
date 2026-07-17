"""
test_cross_browser.py
----------------------
Cross-Browser Test Suite for SauceDemo (https://www.saucedemo.com/)

Note: time.sleep() calls below are added purely so the test run is easy
to watch/observe (slower, visible steps). They are NOT required for the
tests to work correctly -- the actual waits are handled inside pages.py.
"""

import time
from pages import LoginPage, InventoryPage, CartPage, CheckoutPage

VALID_USER = "standard_user"
VALID_PASSWORD = "secret_sauce"
LOCKED_USER = "locked_out_user"

STEP_DELAY = 1.5  # seconds -- increase/decrease to make the run slower/faster


def test_tc01_valid_login(driver, app_url):
    login_page = LoginPage(driver)
    login_page.load(app_url)
    time.sleep(STEP_DELAY)

    login_page.login(VALID_USER, VALID_PASSWORD)
    time.sleep(STEP_DELAY)

    inventory_page = InventoryPage(driver)
    assert inventory_page.is_loaded(), "Inventory page did not load after valid login"
    time.sleep(STEP_DELAY)


def test_tc02_invalid_login(driver, app_url):
    login_page = LoginPage(driver)
    login_page.load(app_url)
    time.sleep(STEP_DELAY)

    login_page.login("invalid_user", "wrong_password")
    time.sleep(STEP_DELAY)

    error_text = login_page.get_error_text()
    assert "Username and password do not match" in error_text
    time.sleep(STEP_DELAY)


def test_tc03_locked_out_user(driver, app_url):
    login_page = LoginPage(driver)
    login_page.load(app_url)
    time.sleep(STEP_DELAY)

    login_page.login(LOCKED_USER, VALID_PASSWORD)
    time.sleep(STEP_DELAY)

    error_text = login_page.get_error_text()
    assert "locked out" in error_text.lower()
    time.sleep(STEP_DELAY)


def test_tc04_sort_price_low_to_high(driver, app_url):
    login_page = LoginPage(driver)
    login_page.load(app_url)
    login_page.login(VALID_USER, VALID_PASSWORD)
    time.sleep(STEP_DELAY)

    inventory_page = InventoryPage(driver)
    inventory_page.is_loaded()
    time.sleep(STEP_DELAY)

    inventory_page.sort_by("Price (low to high)")
    time.sleep(STEP_DELAY)

    prices = inventory_page.get_prices()
    assert prices == sorted(prices), "Products are not sorted low to high"
    time.sleep(STEP_DELAY)


def test_tc05_add_to_cart(driver, app_url):
    login_page = LoginPage(driver)
    login_page.load(app_url)
    login_page.login(VALID_USER, VALID_PASSWORD)
    time.sleep(STEP_DELAY)

    inventory_page = InventoryPage(driver)
    inventory_page.is_loaded()
    time.sleep(STEP_DELAY)

    inventory_page.add_backpack_to_cart()
    time.sleep(STEP_DELAY)

    assert inventory_page.get_cart_count() == "1"
    time.sleep(STEP_DELAY)


def test_tc06_end_to_end_checkout(driver, app_url):
    login_page = LoginPage(driver)
    login_page.load(app_url)
    login_page.login(VALID_USER, VALID_PASSWORD)
    time.sleep(STEP_DELAY)

    inventory_page = InventoryPage(driver)
    inventory_page.is_loaded()
    time.sleep(STEP_DELAY)

    inventory_page.add_backpack_to_cart()
    time.sleep(STEP_DELAY)

    inventory_page.go_to_cart()
    time.sleep(STEP_DELAY)

    cart_page = CartPage(driver)
    assert len(cart_page.items_in_cart()) == 1
    time.sleep(STEP_DELAY)

    cart_page.checkout()
    time.sleep(STEP_DELAY)

    checkout_page = CheckoutPage(driver)
    checkout_page.fill_info("Roshan", "Manandhar", "44600")
    time.sleep(STEP_DELAY)

    checkout_page.finish()
    time.sleep(STEP_DELAY)

    assert checkout_page.get_confirmation_text() == "Thank you for your order!"
    time.sleep(STEP_DELAY)


def test_tc07_logout(driver, app_url):
    login_page = LoginPage(driver)
    login_page.load(app_url)
    login_page.login(VALID_USER, VALID_PASSWORD)
    time.sleep(STEP_DELAY)

    inventory_page = InventoryPage(driver)
    inventory_page.is_loaded()
    time.sleep(STEP_DELAY)

    inventory_page.logout()
    time.sleep(STEP_DELAY)

    assert "saucedemo.com" in driver.current_url
    assert login_page.is_loaded()
    time.sleep(STEP_DELAY)