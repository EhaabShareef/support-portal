# Contract-first Hardware Wizard Changes

This document details all modifications introduced for the contract-first hardware flow with serial capture.

## Livewire Components
- **app/Livewire/OrganizationHardwareWizard.php** – orchestrates contract selection, hardware details, and serial capture steps.
- **app/Livewire/HardwareContractSelector.php** – lists existing contracts and provides inline creation, emitting selection events.
- **app/Livewire/HardwareFormSimple.php** – minimal hardware form capturing type, model, brand, quantity, serial requirement, and remarks.
- **app/Livewire/HardwareSerialManager.php** – manages serial entry up to quantity with uniqueness validation and progress updates.

## Models
- **app/Models/HardwareType.php** – lookup model representing hardware types sourced from Settings.
- **app/Models/HardwareSerial.php** – stores unique serial numbers per hardware item.
- **app/Models/OrganizationHardware.php** – updated with contract, hardware type relations, quantity, and serial-required flag.

## Migrations
- **database/migrations/2025_01_01_000011_create_hardware_types_table.php** – introduces `hardware_types` lookup table.
- **database/migrations/2025_01_01_000012_add_contract_first_fields_to_organization_hardware.php** – adds contract linkage, quantity, and serial-required fields.
- **database/migrations/2025_01_01_000013_create_hardware_serials_table.php** – creates `hardware_serials` table with uniqueness constraint per hardware.

## Seeders
- **database/seeders/HardwareTypesSeeder.php** – seeds baseline hardware types.
- **database/seeders/DatabaseSeeder.php** – registers `HardwareTypesSeeder` for execution.

## Blade Views
- **resources/views/livewire/hardware-contract-selector.blade.php** – UI for selecting or creating contracts.
- **resources/views/livewire/hardware-form-simple.blade.php** – simplified hardware form view.
- **resources/views/livewire/hardware-serial-manager.blade.php** – interface for serial number entry with progress counter.
- **resources/views/livewire/organization-hardware-wizard.blade.php** – parent shell coordinating wizard steps.
- **resources/views/livewire/partials/organization/hardware.blade.php** – listing updated to display brand, quantity, contract badge, and serial progress.

## Routes
- **routes/web.php** – registers route to display the hardware wizard.

## Summary
The system now enforces a contract-first workflow for hardware creation, introduces quantity tracking with optional serial number capture, and provides responsive UI components to manage the process end to end.
