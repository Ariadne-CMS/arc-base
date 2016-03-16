arc/object
==========

This component is all about using composition instead of inheritance. A lot has been written about favoring composition over inheritance. In the case of PHP composition has mostly been confined to Dependency Injection. This is a good development, but composition can be used in many other ways for many other purposes as well. This component defines a number of building blocks to use composition.

Why use composition over inheritance?
-------------------------------------

It is impossible to fully explain the advantages (and disadvantages) of composition over inheritance in the space here. If you want to know all the defails, go google it and make up your own mind. But in short here is one way of looking at it:

Writing code in a dynamic, interpreted language has advantages over writing code in a static, compiled language. Most of the advantages come from something called 'late binding'. This means that in the whole process of writing code - making it executable and running it, dynamic languages defer decisions to the running part, where static languages must resolve all decisions in the 'making it executable' part.

When you write code in a dynamic language using inheritance, you are making alot of the decisions event before the 'making it executable' step, while writing the code. You extend a class, binding the new class to a specific parent class.

Composition allows you to defer that decision to the 'running it' step. Like dependency injection, where only when you pass a dependency to a constructor, so when you actually instantiate a class, the dependency is resolved.