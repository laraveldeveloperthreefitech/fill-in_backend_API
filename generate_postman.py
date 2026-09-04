import json
import os
from urllib.parse import urlparse

def generate_postman_collection(routes_file, output_file):
    with open(routes_file, 'r', encoding='utf-8') as f:
        routes = json.load(f)

    collection = {
        "info": {
            "name": "Fill-In API",
            "description": "API Documentation for Fill-In Laravel Project",
            "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
        },
        "item": []
    }

    folders = {}

    def get_folder_name(uri, action):
        uri_parts = uri.split('/')
        
        if 'admin' in uri_parts:
            return 'Admin'
            
        if 'recruiter' in uri_parts:
            if 'login' in uri_parts or 'registraion' in uri_parts or 'otp' in uri_parts:
                return 'Recruiter - Auth'
            if 'job' in uri_parts or 'shift' in uri_parts or 'booking' in uri_parts:
                return 'Recruiter - Jobs & Bookings'
            if 'profile' in uri_parts or 'clinic' in uri_parts:
                return 'Recruiter - Profile & Clinic'
            return 'Recruiter - Other'
            
        if 'candidate' in uri_parts:
            if 'login' in uri_parts or 'registraion' in uri_parts or 'otp' in uri_parts:
                return 'Candidate - Auth'
            if 'job' in uri_parts or 'apply' in uri_parts or 'interview' in uri_parts:
                return 'Candidate - Jobs & Applications'
            if 'profile' in uri_parts:
                return 'Candidate - Profile'
            return 'Candidate - Other'
            
        if 'broadcasting' in uri_parts:
            return 'Broadcasting'

        return 'Common APIs'

    for route in routes:
        uri = route.get('uri', '')
        
        # Skip internal routes
        if '_ignition' in uri or 'sanctum' in uri or 'api/user' in uri:
            continue
            
        # We only want API routes generally, but this includes all. We can filter out non-api if needed.
        if not uri.startswith('api/') and not uri.startswith('admin/'):
            # Some admin routes are mixed in, we keep them for now.
            pass

        method_str = route.get('method', 'GET')
        methods = method_str.split('|')
        primary_method = 'POST' if 'POST' in methods else methods[0]

        name = route.get('name') or uri
        action = route.get('action', '')
        
        folder_name = get_folder_name(uri, action)
        
        if folder_name not in folders:
            folders[folder_name] = {
                "name": folder_name,
                "item": []
            }
            collection["item"].append(folders[folder_name])
            
        # Extract path parameters
        path_vars = []
        import re
        matches = re.findall(r'\{([^}]+)\}', uri)
        for match in matches:
            path_vars.append({
                "key": match.replace('?', ''),
                "value": "<id>",
                "description": f"{match} parameter"
            })
            
        url_raw = f"{{{{base_url}}}}/{uri.lstrip('/')}"
        
        # Set authorization header based on middleware
        auth_header = []
        middlewares = route.get('middleware', [])
        
        if any('auth' in m for m in middlewares):
            auth_token = "{{recruiter_token}}" if 'recruiter' in uri else "{{candidate_token}}" if 'candidate' in uri else "{{admin_token}}"
            auth_header = [{
                "key": "Authorization",
                "value": f"Bearer {auth_token}",
                "type": "text"
            }]
            
        item = {
            "name": name,
            "request": {
                "method": primary_method,
                "header": [
                    {"key": "Accept", "value": "application/json", "type": "text"}
                ] + auth_header,
                "url": {
                    "raw": url_raw,
                    "host": [
                        "{{base_url}}"
                    ],
                    "path": uri.lstrip('/').split('/'),
                    "variable": path_vars
                },
                "description": f"Controller Action: {action}\nMiddleware: {', '.join(middlewares)}"
            },
            "response": []
        }
        
        # Add basic dummy body for POST/PUT
        if primary_method in ['POST', 'PUT', 'PATCH']:
            item["request"]["body"] = {
                "mode": "raw",
                "raw": "{\n    \n}",
                "options": {
                    "raw": {
                        "language": "json"
                    }
                }
            }
            
        folders[folder_name]["item"].append(item)

    # Sort folders alphabetically
    collection["item"] = sorted(collection["item"], key=lambda x: x["name"])

    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(collection, f, indent=4)

if __name__ == "__main__":
    generate_postman_collection("routes_list_utf8.json", "Fill-In_API_Collection.json")
    print("Postman collection generated successfully.")
