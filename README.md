# Bumip

Bumip is a modern tool for web development. Is not a framework and is not a CMS.
It is an IDT (Integrated Development Tool) built around 4 main principles:

* Every developer has its own style, we will give guidelines, not rules.
* Patterns are continuosly criticized from other parties and tend to become obsolete. We want to get the best from the most popular patterns and concepts.
* KISS: Keep it simple stupid! We want our codebase to be easy to maintain and we want to get you to work as fast as possible.
* Building a real tool to create a real product (not just an API but a complete product) is our main priority. Bumip comes with beautiful customizable UI to kickstart your next creation.

# Core Concepts
Bumip takes advantage from both the **ECS** pattern and the **MVC** pattern.

# Systems
A final Bumip product is composed using **Systems** (called bumip apps), sandboxed microapps (similar to microservices) that you can download and install within the Admin UI or using CLI. A System is more than a module or a package. It can operate by itself, and often comes with a customizable UI written in Vuejs for the frontend and the backend that you can integrate in your SPA or PWA. **You can customize or write your own UI using your preferred language**. There is also a UI builder in the works that can output Vuejs code or block code that you can render with your framework of choiche (even with plain HTML).
Buimip will come with a lot of optional Bumip Apps:
* User App: Signup, Login, Settings, Admin integration. Comes with UI.
* CMS
* Billing App
* Admin app (yes, even the admin is a Bumip App)

## Installing an app from the UI (Importing a System)
![](https://i.gyazo.com/54d398e14045fdae6a576e27973aaf4f.gif)

Apps use adapters to interact with eachother. There will be default adapters but you can extend them or write your own.
**You don't like how Bumip/User deals with security?** Extend the app, build your own user app or install a third party user app.

**But you are not forced to use Systems**. You can build your app using the "extend" folder and use a normal MVC approach. Or use both at the same time.

# Models or Entities

You can create a new Model (Entity) in 3 ways. By building a php model from scratch, by using json-schema or  by using the entity builder in the admin app using the UI. The entity builder creates a json-schema and can be used as it is in php or extended via code.


![](https://media.giphy.com/media/Rlrdn6wkCz7o2mEWtK/giphy.gif)


# Get in touch

If you want to collab with us or get in touch you can join our discord server.
https://discord.gg/XWmRxsh