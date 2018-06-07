<?php
App::uses('AppController', 'Controller');

class OrdersController extends AppController {

	public $uses = array();
	
	public $components = array('RequestHandler');
	
	/*Method for read the json of products*/
	public function readProducts(){
		$this->products = json_decode(file_get_contents("../../data/products.json"));
	}

	/*Method for read the json of customers*/
	public function readCustomers(){
		$this->customers = json_decode(file_get_contents("../../data/customers.json"));
	}
	
	/*Function to get the renevue of a customer
		params: $id -> id of the customer
	*/
	public function getCustomerRevenue($id){
		foreach($this->customers as $customer){
			if($customer->id==$id){
				if(isset($customer->revenue)) return $customer->revenue;
				break;
			}
		}
		return 0;
	}
	
	/*Function to get a producto by their id
		params: $id -> id of the product to return
	*/
	public function getProductById($id){
		foreach($this->products as $product){
			if($product->id==$id){
				return $product;
			}
		}
		return false;
	}

	/*Function to get a cheappes product of a category
		params: $order -> array list of element of the order
				$category -> category id to match with products
				$minCount -> minimum amount of products to find
	*/
	public function getCheapestProductOfCategoryIfMoreThanX($order,$category,$minCount){
		$count = 0;
		$cheapestProduct=false;
		foreach($order->items as $item){
			$product = $this->getProductById($item->{"product-id"});
			if($product){
				if($product->category==$category){
					$count += $item->quantity;
					
					if($cheapestProduct==false || $cheapestProduct->{"unit-price"}>$item->{"unit-price"}){
						$cheapestProduct=$item;
					}
					
				}
			}
		}
		if($count>=$minCount) return $cheapestProduct;
		else return false;
	}
	
	/*Function to get a list of products of an especify category
		params: $order -> array list of element of the order
				$category -> category id to match with products
				$minCount -> minimum amount of products to find
	*/
	public function getProductsOfCategoryIfMoreThanX($order,$category,$minCount){

		$products=array();
		foreach($order->items as $item){
			$product = $this->getProductById($item->{"product-id"});
			if($product){
				if($product->category==$category && $item->quantity>$minCount){
					$products[]=$item;
				}
			}
		}
		return $products;
	}

	/*API Function to get an order discounts
	*/
	public function discount() {
		$this->autoRender = false;
		
		$response = array(
			'totalDiscount' => 0,
			'discountDescriptions' => array()
		);
		
		if(!empty($this->request->data)){
			$order = json_decode(json_encode($this->request->data));
		
			$this->readProducts();
			$this->readCustomers();
		
			$discountsRules = json_decode(file_get_contents("../../discounts.json"));

			$revenue = $this->getCustomerRevenue($order->{"customer-id"});

			foreach($discountsRules->renevueByUser as $rule){
				$amountLimit = $rule->amount;
				$porcentageDiscount = $rule->porcentageToDiscount;
		
				if($revenue>=$amountLimit){ //Obtiene 10% de descuento en toda la compra
					$discount = $order->total* $porcentageDiscount/100;
					$response['totalDiscount'] += $discount;
					$response['discountDescriptions'][] = array(
						'amount' => number_format($discount,2),
						'reason' => __('A customer who has already bought for over € %s, gets a discount of %s% on the whole order.',$amountLimit,$porcentageDiscount)
					);
				}
			}
		
			foreach($discountsRules->cheapestOfCategory as $rule){
				$category = $rule->category;
				$minNumberOfProducts = $rule->products;
				$porcentageDiscount = $rule->porcentageToDiscount;
		
				$cheapestProduct = $this->getCheapestProductOfCategoryIfMoreThanX($order,$category,$minNumberOfProducts);
				if($cheapestProduct){
					$discount = $cheapestProduct->{"unit-price"} * $porcentageDiscount/100;
					$response['totalDiscount'] += $discount;
					$response['discountDescriptions'][] = array(
						'amount' => number_format($discount,2),
						'reason' => __('If you buy %s or more products of category with id %s, you get a %s% discount on the cheapest product.',$minNumberOfProducts,$category,$porcentageDiscount)
					);
			
				}
			}
		
			foreach($discountsRules->takeXPayY as $rule){
				$category = $rule->category;
				$minNumberOfProducts = $rule->products;
				$itemsToDiscount = $rule->itemsToDiscount;
		
				$products = $this->getProductsOfCategoryIfMoreThanX($order,$category,$minNumberOfProducts);
				foreach($products as $product){
					$countFree = intval(($product->{"quantity"})/$minNumberOfProducts);
			
					$countFree = intval(($product->{"quantity"}-$countFree)/$minNumberOfProducts);
			
					$discount = $product->{"unit-price"}*$countFree;
			
					$response['totalDiscount'] += $discount;
					$response['discountDescriptions'][] = array(
						'amount' => number_format($discount,2),
						'reason' => __('For every product of category id %s, when you buy %s, you get %s for free.',$category,$minNumberOfProducts,$itemsToDiscount)
					);
				}
			}
		
			$response['totalDiscount'] = number_format($response['totalDiscount'],2);
		}
		
		
		echo json_encode($response);
	}
	
}
