function addCart(name, price){
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    cart.push({name, price});
    localStorage.setItem("cart", JSON.stringify(cart));

    alert(name + " added to cart");
}

// SHOW CART
if(document.getElementById("cart")){
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    let output = "";

    cart.forEach(item=>{
        output += `
        <div class="card">
            <div class="card-content">
                <h3>${item.name}</h3>
                <p>Rs ${item.price}</p>
            </div>
        </div>
        `;
    });

    document.getElementById("cart").innerHTML = output;
}