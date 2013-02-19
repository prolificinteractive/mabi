{
    
    "name": "Foodtweeks",
    
    "classes": [
        
        {
            
            /**
             * Basic meta for model
             *
             * @param String name The internal name of the model.
             * @param String namePlural The pluralized name of the model. If omitted, this will be inferred at compile time.
             * @param String modelName The cannonical name for the model, if your API prefers it.
             * @param Array comments List of comments (will be converted into separate lines).
             */
            
            "name": "Food",
            
            /* Optional */
            "namePlural": "Foods",
            
            /* Optional */
            "modelName": "FoodTweekModel",
            
            /* Optional; Split into separate lines */
            "comments": [
                "Created by Gregory Boland on 2/12/13.",
                "Copyright (c) 2013 Gregory Boland. All rights reserved."
            ],
            
            /**
             * Imports for the header files for iOS
             */
            
            "imports": [
                "Foundation/Foundation.h",
                "MabiModel.h"
            ],
            
            /**
             * Properties for the model
             *
             * Available property keys are:
             * @param String name Name of the property
             * @param MABI_MODEL_PROPERTY_TYPES type Type of property
             */
            
            "properties": [
                
                {
                    "name": "foodID",
                    "type": "2"
                },
                
                {
                    "name": "imageURL",
                    "type": "0"
                },
                
                {
                    "name": "foodName",
                    "type": "0"
                },
                
                {
                    "name": "partnerID",
                    "type": "2"
                },
                
                {
                    "name": "tweeks",
                    "type": "1"
                }
                
            ] /* Properties */
            
        } /* Class "Food" */
        
    ] /* Classes */
    
}