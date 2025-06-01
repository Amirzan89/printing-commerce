<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Jasa;
use App\Models\JasaImage;

class JasaImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Make sure the destination directories exist
        $thumbnailDir = public_path('assets3/img/jasa');
        $galleryDir = public_path('assets3/img/jasa/gallery');
        
        if (!File::exists($thumbnailDir)) {
            File::makeDirectory($thumbnailDir, 0755, true);
        }
        
        if (!File::exists($galleryDir)) {
            File::makeDirectory($galleryDir, 0755, true);
        }
        
        // Get all jasas
        $jasas = Jasa::all();
        
        // Source directories
        $sourceDir = base_path('seeder/resources/img/jasa');
        
        if (!File::exists($sourceDir)) {
            $this->command->error('Source directory not found: ' . $sourceDir);
            return;
        }
        
        // Process each jasa
        foreach ($jasas as $jasa) {
            $this->seedImagesForJasa($jasa, $sourceDir, $thumbnailDir, $galleryDir);
        }
        
        $this->command->info('JasaImage seeding completed successfully!');
    }
    
    /**
     * Seed images for a specific jasa
     * 
     * @param Jasa $jasa
     * @param string $sourceDir
     * @param string $thumbnailDir
     * @param string $galleryDir
     */
    private function seedImagesForJasa($jasa, $sourceDir, $thumbnailDir, $galleryDir)
    {
        // If the category-specific folder exists, use that
        $categoryDir = $sourceDir . '/' . $jasa->kategori;
        if (!File::exists($categoryDir)) {
            $categoryDir = $sourceDir; // Fallback to main source directory
        }
        
        // Get all image files from the source directory
        $imageFiles = collect(File::files($categoryDir))
            ->filter(function ($file) {
                return in_array($file->getExtension(), ['jpg', 'jpeg', 'png']);
            })
            ->shuffle() // Randomize the order
            ->values(); // Reset keys
        
        if ($imageFiles->isEmpty()) {
            $this->command->warn("No images found for jasa {$jasa->nama_jasa} in {$categoryDir}");
            return;
        }
        
        // Use the first image as thumbnail if the jasa doesn't already have one
        if (empty($jasa->thumbnail_jasa) && $imageFiles->isNotEmpty()) {
            $thumbnailFile = $imageFiles->shift();
            $thumbnailName = $jasa->id_jasa . '_' . uniqid() . '.' . $thumbnailFile->getExtension();
            
            // Copy the file to the thumbnail directory
            File::copy($thumbnailFile->getPathname(), $thumbnailDir . '/' . $thumbnailName);
            
            // Update the jasa record
            $jasa->thumbnail_jasa = $thumbnailName;
            $jasa->save();
            
            $this->command->info("Thumbnail added for jasa {$jasa->nama_jasa}: {$thumbnailName}");
        }
        
        // Use 2-4 random images for the gallery
        $galleryCount = min(rand(2, 4), $imageFiles->count());
        $galleryFiles = $imageFiles->take($galleryCount);
        
        foreach ($galleryFiles as $index => $file) {
            $imageName = $jasa->id_jasa . '_gallery_' . ($index + 1) . '_' . uniqid() . '.' . $file->getExtension();
            
            // Copy the file to the gallery directory
            File::copy($file->getPathname(), $galleryDir . '/' . $imageName);
            
            // Create a record in the database
            JasaImage::create([
                'id_jasa' => $jasa->id_jasa,
                'image_path' => $imageName
            ]);
            
            $this->command->info("Gallery image added for jasa {$jasa->nama_jasa}: {$imageName}");
        }
    }
} 