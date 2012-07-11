Projection
==========

What is Projection?
-------------------

Projection is a very simple command-line tool that allows you to manage your
open source PHP projects auto-magically. It can generate classes, unit tests
and much more!

How to install it?
------------------

To install Projection run the following commands from your command line:

    $ git clone git://github.com/alessandro1997/Projection.git ~/projection
    $ cd ~/projection
    $ wget http://getcomposer.org/composer.phar
    $ php composer.phar install

This will download Projection in the **projection** directory contained in your
home folder. It will also download the third-party libraries so that Projection
will work correctly.

Now we must ensure that the **projection** executable is in your $PATH. To do
so, edit your **~/.bashrc** file and add this at the end:

    export PATH=$PATH:~/projection

After you hit Enter, you will have to restart your shell for the changes to
take effect. To make sure everything worked, type the following in a shell:

    $ projection -v

It should display Projection's version number.

How to use it?
--------------

Projection uses the [Console](https://github.com/symfony/Console) component of
the [Symfony](http://www.symfony.com) framework, so if you've ever used the
console of a Symfony application you're already familiar with it.

### Your first project

So, now that we're all set, let's create our first project. Open a shell and
type:

    $ projection generate:project

And follow the instructions. The needed files and directories will be created.

Note: you can shorten commands, so ```generate:project``` is the same as
```g:p``` or ```gen:pro```. Wonder how? Ask the Symfony team!

You probably want to customize your **README.md**, **LICENSE** and
**phpunit.xml.dist**.

Okay, so now you have a project which is managed by Projection. The first thing
you should do now is create a class using the ```generate:class``` command.

We'll leave that to you. Good luck!

License
-------

Projection is developed by [Alessandro Desantis](http://about.me/alessandro1997)
and proudly released to the open source community under the MIT license.
