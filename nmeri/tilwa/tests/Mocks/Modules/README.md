## Introduction

ModuleOne is the sandbox used for testing a majority of functionality, with the exception of those requiring concrete methods/signatures/content/behavior that don't exist on ModuleOne. Testing communication/inter-module dependency falls into the latter category.

Trivial config replacements should use `ModuleLevelTest\replicateModule`

## Description

This tests for and confirms modules are recursively loaded i.e. children of the outermost are automatically booted and properly injected 