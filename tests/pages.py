"""
pages.py
--------
Simple Page Object Model (POM) helpers for the SauceDemo application.
"""

import time
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import (
    ElementClickInterceptedException,
    StaleElementReferenceException,
)

WAIT_TIMEOUT = 25


def safe_click(driver, wait, locator):
    for attempt in range(2):
        try:
            element = wait.until(EC.element_to_be_clickable(locator))
            driver.execute_script(
                "arguments[0].scrollIntoView({block: 'center'});", element
            )
            time.sleep(0.4)
            driver.execute_script("arguments[0].click();", element)
            return element
        except (ElementClickInterceptedException, StaleElementReferenceException):
            if attempt == 1:
                raise
            continue


class LoginPage:
    USERNAME_INPUT = (By.ID, "user-name")
    PASSWORD_INPUT = (By.ID, "password")
    LOGIN_BUTTON = (By.ID, "login-button")
    ERROR_MESSAGE = (By.CSS_SELECTOR, "[data-test='error']")

    def __init__(self, driver):
        self.driver = driver
        self.wait = WebDriverWait(driver, WAIT_TIMEOUT)

    def load(self, app_url):
        self.driver.get(app_url)

    def login(self, username, password):
        user_field = self.wait.until(EC.visibility_of_element_located(self.USERNAME_INPUT))
        user_field.clear()
        user_field.send_keys(username)

        pass_field = self.driver.find_element(*self.PASSWORD_INPUT)
        pass_field.clear()
        pass_field.send_keys(password)

        safe_click(self.driver, self.wait, self.LOGIN_BUTTON)

    def get_error_text(self):
        return self.wait.until(
            EC.visibility_of_element_located(self.ERROR_MESSAGE)
        ).text

    def is_loaded(self):
        return self.wait.until(
            EC.visibility_of_element_located(self.LOGIN_BUTTON)
        ) is not None


class InventoryPage:
    PAGE_TITLE = (By.CLASS_NAME, "title")
    SORT_DROPDOWN = (By.CLASS_NAME, "product_sort_container")
    PRODUCT_PRICES = (By.CLASS_NAME, "inventory_item_price")
    ADD_TO_CART_BACKPACK = (By.ID, "add-to-cart-sauce-labs-backpack")
    CART_BADGE = (By.CLASS_NAME, "shopping_cart_badge")
    CART_LINK = (By.CLASS_NAME, "shopping_cart_link")
    MENU_BUTTON = (By.ID, "react-burger-menu-btn")
    LOGOUT_LINK = (By.ID, "logout_sidebar_link")

    def __init__(self, driver):
        self.driver = driver
        self.wait = WebDriverWait(driver, WAIT_TIMEOUT)

    def is_loaded(self):
        return self.wait.until(
            EC.visibility_of_element_located(self.PAGE_TITLE)
        ).text == "Products"

    def sort_by(self, visible_text):
        dropdown = self.wait.until(EC.element_to_be_clickable(self.SORT_DROPDOWN))
        Select(dropdown).select_by_visible_text(visible_text)

    def get_prices(self):
        elements = self.wait.until(
            EC.presence_of_all_elements_located(self.PRODUCT_PRICES)
        )
        return [float(e.text.replace("$", "")) for e in elements]

    def add_backpack_to_cart(self):
        safe_click(self.driver, self.wait, self.ADD_TO_CART_BACKPACK)
        self.wait.until(EC.visibility_of_element_located(self.CART_BADGE))

    def get_cart_count(self):
        return self.wait.until(
            EC.visibility_of_element_located(self.CART_BADGE)
        ).text

    def go_to_cart(self):
        safe_click(self.driver, self.wait, self.CART_LINK)
        self.wait.until(EC.url_contains("cart.html"))

    def logout(self):
        safe_click(self.driver, self.wait, self.MENU_BUTTON)
        time.sleep(0.5)
        safe_click(self.driver, self.wait, self.LOGOUT_LINK)
        self.wait.until(EC.visibility_of_element_located(LoginPage.LOGIN_BUTTON))


class CartPage:
    CHECKOUT_BUTTON = (By.ID, "checkout")
    CART_ITEM = (By.CLASS_NAME, "cart_item")

    def __init__(self, driver):
        self.driver = driver
        self.wait = WebDriverWait(driver, WAIT_TIMEOUT)

    def items_in_cart(self):
        return self.wait.until(
            EC.presence_of_all_elements_located(self.CART_ITEM)
        )

    def checkout(self):
        safe_click(self.driver, self.wait, self.CHECKOUT_BUTTON)
        self.wait.until(EC.url_contains("checkout-step-one.html"))


class CheckoutPage:
    FIRST_NAME = (By.ID, "first-name")
    LAST_NAME = (By.ID, "last-name")
    ZIP_CODE = (By.ID, "postal-code")
    CONTINUE_BUTTON = (By.ID, "continue")
    FINISH_BUTTON = (By.ID, "finish")
    COMPLETE_HEADER = (By.CLASS_NAME, "complete-header")
    ERROR_MESSAGE = (By.CSS_SELECTOR, "[data-test='error']")

    def __init__(self, driver):
        self.driver = driver
        self.wait = WebDriverWait(driver, WAIT_TIMEOUT)

    def _safe_type(self, locator, text):
        field = self.wait.until(EC.visibility_of_element_located(locator))
        self.driver.execute_script(
            """
            const el = arguments[0];
            const value = arguments[1];
            const nativeSetter = Object.getOwnPropertyDescriptor(
                window.HTMLInputElement.prototype, 'value'
            ).set;
            nativeSetter.call(el, value);
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
            """,
            field, text
        )
        time.sleep(0.2)
        actual = field.get_attribute("value")
        if actual != text:
            raise AssertionError(
                f"Failed to type '{text}' into field {locator} (got '{actual}')"
            )

    def fill_info(self, first, last, zip_code):
        self._safe_type(self.FIRST_NAME, first)
        self._safe_type(self.LAST_NAME, last)
        self._safe_type(self.ZIP_CODE, zip_code)

        safe_click(self.driver, self.wait, self.CONTINUE_BUTTON)

        try:
            self.wait.until(EC.url_contains("checkout-step-two.html"))
        except Exception:
            error_elements = self.driver.find_elements(*self.ERROR_MESSAGE)
            if error_elements:
                raise AssertionError(
                    f"Checkout form validation failed: {error_elements[0].text}"
                )
            raise

    def finish(self):
        safe_click(self.driver, self.wait, self.FINISH_BUTTON)
        self.wait.until(EC.visibility_of_element_located(self.COMPLETE_HEADER))

    def get_confirmation_text(self):
        return self.wait.until(
            EC.visibility_of_element_located(self.COMPLETE_HEADER)
        ).text