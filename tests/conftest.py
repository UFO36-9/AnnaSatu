"""
conftest.py
------------
Shared pytest configuration for Cross-Browser Testing.

This file defines the `driver` fixture which launches a fresh WebDriver
instance for whichever browser is passed via the --run-browser command
line option (chrome / firefox / edge). Selenium's built-in Selenium
Manager automatically downloads the correct driver binary, so no
separate driver manager package is needed.
"""

import pytest
from selenium import webdriver


def pytest_addoption(parser):
    """Adds the --run-browser command line flag to pytest."""
    parser.addoption(
        "--run-browser",
        action="store",
        default="chrome",
        help="Browser to run tests on: chrome, firefox, or edge",
    )


@pytest.fixture
def driver(request):
    """
    Creates and returns a WebDriver instance for the browser specified
    with --run-browser. Automatically closes (quits) the browser after
    each test finishes, whether the test passed or failed.
    """
    browser_name = request.config.getoption("--run-browser").lower()

    if browser_name == "chrome":
        options = webdriver.ChromeOptions()
        options.add_argument("--start-maximized")
        driver_instance = webdriver.Chrome(options=options)

    elif browser_name == "firefox":
        options = webdriver.FirefoxOptions()
        options.add_argument("--start-maximized")
        driver_instance = webdriver.Firefox(options=options)

    elif browser_name == "edge":
        options = webdriver.EdgeOptions()
        options.add_argument("--start-maximized")
        driver_instance = webdriver.Edge(options=options)

    else:
        raise ValueError(
            f"Unsupported browser: '{browser_name}'. "
            f"Use one of: chrome, firefox, edge "
            f"(Safari is supported only when tests are run natively on macOS)."
        )

    driver_instance.implicitly_wait(10)
    yield driver_instance
    driver_instance.quit()


@pytest.fixture
def app_url():
    """The application under test."""
    return "https://www.saucedemo.com/"